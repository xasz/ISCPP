<?php

use App\Models\SCBillable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('guests are redirected to the login page for scbillables', function () {
    $response = $this->get('/scbillables');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scbillables index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scbillables');
    $response->assertStatus(200);
});

test('billables of a tenant that no longer exists still render', function () {
    $billable = SCBillable::query()->create([
        'month' => now()->month,
        'year' => now()->year,
        'tenantId' => (string) Str::uuid(),
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

    $this->actingAs(User::factory()->create());

    $this->get(route('scbillables.index'))
        ->assertSuccessful()
        ->assertSee('Endpoint Protection')
        ->assertSee('Unknown');

    $this->get(route('scbillables.show', $billable))
        ->assertSuccessful()
        ->assertSee('Unknown');
});
