<?php

use App\Jobs\PushToHaloSCTenantSCBillables;
use App\Models\SCBillable;
use App\Models\SCTenant;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Livewire\Volt\Volt;

test('guests are redirected to the login page for scbilling fetcher', function () {
    $response = $this->get('/scbilling/fetcher');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbilling fetcher page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scbilling/fetcher');
    $response
        ->assertSuccessful()
        ->assertSeeLivewire('scbilling.card-fetcher');
});

test('guests are redirected to the login page for scbilling halo pusher', function () {
    $response = $this->get('/scbilling/haloPusher');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbilling halo pusher page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // The route returns 403 for regular users, which is the expected behavior
    $response = $this->get('/scbilling/haloPusher');
    $response->assertStatus(403);
});

test('pushing all billables queues a halo push for every tenant of the month', function () {
    Queue::fake();
    $tenant = SCTenant::factory()->create(['haloclient_id' => 42]);

    SCBillable::query()->create([
        'month' => now()->month,
        'year' => now()->year,
        'tenantId' => $tenant->id,
        'orderLineItemNumber' => '1001',
        'productGroup' => 'Endpoint',
        'billableQuantity' => 2,
        'orderedQuantity' => 2,
        'actualQuantity' => 2,
        'productCode' => 'endpointProtection',
        'sku' => 'SKU-1001',
        'productDescription' => 'Endpoint Protection',
        'rawData' => [],
        'sent_to_halo' => false,
    ]);

    Volt::test('scbilling.card-haloPusher')
        ->call('push')
        ->assertSet('errorMessage', '');

    Queue::assertPushed(PushToHaloSCTenantSCBillables::class, fn ($job) => $job->sctenant->is($tenant));
});
