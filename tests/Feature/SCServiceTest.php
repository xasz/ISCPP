<?php

use App\Models\SCTenant;
use App\Services\SCService;
use App\Settings\SCServiceSettings;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sleep::fake();

    $settings = resolve(SCServiceSettings::class);
    $settings->token = 'test-token';
    $settings->token_expires_at = time() + 3600;
    $settings->save();

    $this->tenant = SCTenant::factory()->create([
        'apiHost' => 'https://api-eu02.central.sophos.com',
    ]);
});

test('retries rate limited and server error responses with millisecond backoff', function () {
    Http::fakeSequence('api-eu02.central.sophos.com/*')
        ->push(['error' => 'tooManyRequests'], 429)
        ->push(['error' => 'serviceUnavailable'], 503)
        ->push(['items' => [['id' => 'endpoint-1']], 'pages' => ['total' => 1]], 200);

    $sleptMilliseconds = [];
    Sleep::whenFakingSleep(function (CarbonInterval $duration) use (&$sleptMilliseconds) {
        $sleptMilliseconds[] = $duration->totalMilliseconds;
    });

    $endpoints = resolve(SCService::class)->endpoints($this->tenant);

    expect($endpoints->pluck('id')->all())->toBe(['endpoint-1']);
    Http::assertSentCount(3);
    expect($sleptMilliseconds)->toHaveCount(2)
        ->and($sleptMilliseconds[0])->toBeLessThanOrEqual(1000)
        ->and($sleptMilliseconds[1])->toBeLessThanOrEqual(2000);
});

test('gives up after max retries and throws instead of returning the error body', function () {
    Http::fake(['api-eu02.central.sophos.com/*' => Http::response(['error' => 'tooManyRequests'], 429)]);

    expect(fn () => resolve(SCService::class)->endpoints($this->tenant))
        ->toThrow(Exception::class, 'Sophos Partner API Error: 429');

    Http::assertSentCount(11);
    Sleep::assertSlept(fn (CarbonInterval $duration) => $duration->totalMilliseconds <= 30000, 10);
});

test('does not retry client errors and throws', function () {
    Http::fake(['api-eu02.central.sophos.com/*' => Http::response(['error' => 'unauthorized'], 401)]);

    expect(fn () => resolve(SCService::class)->endpoints($this->tenant))
        ->toThrow(Exception::class, 'Sophos Partner API Error: 401');

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

test('returns the error body when the status code check is skipped', function () {
    Http::fake(['api-eu02.central.sophos.com/*' => Http::response(['error' => 'badRequest', 'message' => 'Invalid firewall'], 400)]);

    $response = resolve(SCService::class)->firewallFirmwareUpgradeCheck($this->tenant, ['firewall-1']);

    expect($response)->toBe(['error' => 'badRequest', 'message' => 'Invalid firewall']);
});
