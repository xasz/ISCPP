<?php

use App\Jobs\CreateSCFirewallBackup;
use App\Jobs\PollSCFirewallBackup;
use App\Models\SCFirewall;
use App\Models\SCFirewallBackup;
use App\Models\SCTenant;
use App\Models\User;
use App\Services\SCService;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use Livewire\Volt\Volt;

beforeEach(function () {
    Sleep::fake();
    Storage::fake(SCFirewallBackup::DISK);

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->firewallsScheduleEnabled = true;
    $settings->firewallBackupsRetention = 2;
    $settings->save();

    $this->tenant = SCTenant::factory()->create(['apiHost' => 'https://api-eu02.central.sophos.com']);
    $this->firewall = SCFirewall::factory()->forTenant($this->tenant)->create([
        'hostname' => 'fw-edge-01',
        'rawData' => ['status' => ['connected' => true, 'suspended' => false], 'serialNumber' => 'X1'],
    ]);
    $this->apiBase = 'api-eu02.central.sophos.com/firewall/v1/firewall-config/firewalls/'.$this->firewall->id;
});

function pendingBackup(SCFirewall $firewall): SCFirewallBackup
{
    return SCFirewallBackup::factory()->forFirewall($firewall)->create(['transactionId' => 'b1c2d3e4-f5a6-4789-abcd-ef0123456789']);
}

test('creating a backup starts an export and schedules the first poll', function () {
    Queue::fake([PollSCFirewallBackup::class]);
    Http::fake([$this->apiBase.'/export' => Http::response(['transactionId' => 'b1c2d3e4-f5a6-4789-abcd-ef0123456789'], 202)]);

    CreateSCFirewallBackup::dispatchSync($this->firewall->id, 'admin@example.com');

    $backup = SCFirewallBackup::sole();

    expect($backup->status)->toBe('pending')
        ->and($backup->transactionId)->toBe('b1c2d3e4-f5a6-4789-abcd-ef0123456789')
        ->and($backup->requested_by)->toBe('admin@example.com');

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/export') && $request['fullExport'] === true);
    Queue::assertPushed(PollSCFirewallBackup::class, fn (PollSCFirewallBackup $job) => $job->backupId === $backup->id && $job->poll === 1);
});

test('a rejected export is stored as a failed backup', function () {
    Queue::fake([PollSCFirewallBackup::class]);
    Http::fake([$this->apiBase.'/export' => Http::response(['error' => 'forbidden'], 403)]);

    CreateSCFirewallBackup::dispatchSync($this->firewall->id);

    expect(SCFirewallBackup::sole()->status)->toBe('failed')
        ->and(SCFirewallBackup::sole()->error)->toContain('403');
    Queue::assertNotPushed(PollSCFirewallBackup::class);
});

test('a finished export is downloaded and stored', function () {
    $backup = pendingBackup($this->firewall);
    Http::fake([
        $this->apiBase.'/transactions/*' => Http::response([
            'id' => $backup->transactionId,
            'status' => 'finished',
            'result' => 'success',
            'response' => ['url' => 'https://example-bucket.s3.amazonaws.com/exports/config.tar.gz?X-Amz-Signature=abc', 'method' => 'GET'],
        ]),
        'example-bucket.s3.amazonaws.com/*' => Http::response('TARBALL'),
    ]);

    PollSCFirewallBackup::dispatchSync($backup->id);

    $backup->refresh();

    expect($backup->status)->toBe('finished')
        ->and($backup->file_size)->toBe(7)
        ->and($backup->sha256)->toBe(hash('sha256', 'TARBALL'))
        ->and($backup->file_path)->toContain('fw-edge-01');
    Storage::disk(SCFirewallBackup::DISK)->assertExists($backup->file_path);

    // The signed download link must not carry the Sophos bearer token.
    Http::assertSent(fn ($request) => str_contains($request->url(), 's3.amazonaws.com') && ! $request->hasHeader('Authorization'));
});

test('an export that is still running is polled again later', function () {
    Queue::fake([PollSCFirewallBackup::class]);
    $backup = pendingBackup($this->firewall);
    Http::fake([$this->apiBase.'/transactions/*' => Http::response(['id' => $backup->transactionId, 'status' => 'started', 'result' => 'notAvailable'])]);

    (new PollSCFirewallBackup($backup->id, 3))->handle(app(SCService::class));

    expect($backup->fresh()->status)->toBe('pending');
    Queue::assertPushed(PollSCFirewallBackup::class, fn (PollSCFirewallBackup $job) => $job->poll === 4);
});

test('an export that never finishes fails after the last poll', function () {
    Queue::fake([PollSCFirewallBackup::class]);
    $backup = pendingBackup($this->firewall);
    Http::fake([$this->apiBase.'/transactions/*' => Http::response(['id' => $backup->transactionId, 'status' => 'pending'])]);

    (new PollSCFirewallBackup($backup->id, PollSCFirewallBackup::MAX_POLLS))->handle(app(SCService::class));

    expect($backup->fresh()->status)->toBe('failed')
        ->and($backup->fresh()->error)->toContain('did not finish');
    Queue::assertNotPushed(PollSCFirewallBackup::class);
});

test('an export that finished with an error is stored as failed', function () {
    $backup = pendingBackup($this->firewall);
    Http::fake([$this->apiBase.'/transactions/*' => Http::response(['id' => $backup->transactionId, 'status' => 'finished', 'result' => 'error', 'response' => ['message' => 'Export not supported']])]);

    PollSCFirewallBackup::dispatchSync($backup->id);

    expect($backup->fresh()->status)->toBe('failed')
        ->and($backup->fresh()->error)->toContain('Export not supported');
});

test('only the configured number of finished backups is kept', function () {
    $disk = Storage::disk(SCFirewallBackup::DISK);
    foreach (['old', 'middle', 'new'] as $index => $name) {
        $disk->put("firewall-backups/{$name}.tar.gz", $name);
        SCFirewallBackup::factory()->forFirewall($this->firewall)->finished("firewall-backups/{$name}.tar.gz")->create(['created_at' => now()->subDays(3 - $index)]);
    }

    SCFirewallBackup::pruneFor($this->firewall->id, 2);

    expect(SCFirewallBackup::pluck('file_path')->sort()->values()->all())
        ->toBe(['firewall-backups/middle.tar.gz', 'firewall-backups/new.tar.gz']);
    $disk->assertMissing('firewall-backups/old.tar.gz');
});

test('the scheduled command queues backups only for connected firewalls of active tenants', function () {
    Queue::fake();
    $disconnected = SCFirewall::factory()->forTenant($this->tenant)->create(['rawData' => ['status' => ['connected' => false]]]);
    $ignoredTenant = SCTenant::factory()->create(['iscpp_ignore' => true]);
    SCFirewall::factory()->forTenant($ignoredTenant)->create(['rawData' => ['status' => ['connected' => true]]]);

    $this->artisan('app:queue-scfirewall-backups-for-all-tenants')->assertSuccessful();

    Queue::assertPushed(CreateSCFirewallBackup::class, 1);
    Queue::assertPushed(CreateSCFirewallBackup::class, fn (CreateSCFirewallBackup $job) => $job->firewallId === $this->firewall->id);
});

describe('backups tab', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->create(['email' => 'admin@example.com']));
    });

    test('lists backups and offers finished ones for download', function () {
        Storage::disk(SCFirewallBackup::DISK)->put('firewall-backups/a.tar.gz', 'DATA');
        $backup = SCFirewallBackup::factory()->forFirewall($this->firewall)->finished('firewall-backups/a.tar.gz')->create();

        $this->get(route('scfirewalls.firewallBackups', ['id' => $this->firewall->id]))
            ->assertOk()
            ->assertSee(route('scfirewalls.backupDownload', ['id' => $this->firewall->id, 'backup' => $backup->id]), false);

        $this->get(route('scfirewalls.backupDownload', ['id' => $this->firewall->id, 'backup' => $backup->id]))
            ->assertOk()
            ->assertDownload('a.tar.gz');
    });

    test('a backup of another firewall cannot be downloaded through this firewall', function () {
        $other = SCFirewall::factory()->forTenant($this->tenant)->create();
        Storage::disk(SCFirewallBackup::DISK)->put('firewall-backups/b.tar.gz', 'DATA');
        $backup = SCFirewallBackup::factory()->forFirewall($other)->finished('firewall-backups/b.tar.gz')->create();

        $this->get(route('scfirewalls.backupDownload', ['id' => $this->firewall->id, 'backup' => $backup->id]))
            ->assertNotFound();
    });

    test('the create button queues a backup for a connected firewall', function () {
        Queue::fake();

        Volt::test('scfirewalls.backups', ['firewall' => $this->firewall])
            ->call('createBackup')
            ->assertSee('Backup queued');

        Queue::assertPushed(CreateSCFirewallBackup::class, fn (CreateSCFirewallBackup $job) => $job->requestedBy === 'admin@example.com');
    });

    test('the create button refuses a disconnected firewall and a running backup', function () {
        Queue::fake();
        $disconnected = SCFirewall::factory()->forTenant($this->tenant)->create(['rawData' => ['status' => ['connected' => false]]]);
        pendingBackup($this->firewall);

        Volt::test('scfirewalls.backups', ['firewall' => $disconnected])->call('createBackup')->assertSee('not connected');
        Volt::test('scfirewalls.backups', ['firewall' => $this->firewall])->call('createBackup')->assertSee('already running');

        Queue::assertNothingPushed();
    });

    test('a backup can be deleted together with its file', function () {
        Storage::disk(SCFirewallBackup::DISK)->put('firewall-backups/c.tar.gz', 'DATA');
        $backup = SCFirewallBackup::factory()->forFirewall($this->firewall)->finished('firewall-backups/c.tar.gz')->create();

        Volt::test('scfirewalls.backups', ['firewall' => $this->firewall])->call('deleteBackup', $backup->id);

        expect(SCFirewallBackup::count())->toBe(0);
        Storage::disk(SCFirewallBackup::DISK)->assertMissing('firewall-backups/c.tar.gz');
    });
});

test('the feature settings store the backup schedule and retention', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    Volt::test('settings.card-sc-features')
        ->set('firewallBackupsScheduleEnabled', true)
        ->set('firewallBackupsRetention', 14)
        ->call('save')
        ->assertHasNoErrors();

    $settings = resolve(SCServiceSettings::class);
    $settings->refresh();

    expect($settings->firewallBackupsScheduleEnabled)->toBeTrue()
        ->and($settings->firewallBackupsRetention)->toBe(14);
});

test('the firewall list can be filtered by connection state', function () {
    $this->actingAs(User::factory()->create());
    SCFirewall::factory()->forTenant($this->tenant)->create(['hostname' => 'fw-offline', 'rawData' => ['status' => ['connected' => false]]]);

    $this->get(route('scfirewalls.index', ['filterStatus' => 'disconnected']))
        ->assertOk()
        ->assertSee('fw-offline')
        ->assertDontSee('fw-edge-01');
});
