<?php

use App\Models\SCAlert;
use App\Models\SCTenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page for scalerts', function () {
    $response = $this->get('/scalerts');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the scalerts index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/scalerts');
    $response->assertStatus(200);
});

test('authenticated users can view a specific scalert', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Create a tenant first, then create an alert with that tenant ID
    $tenant = SCTenant::factory()->create();
    $alert = SCAlert::factory()->create([
        'tenantId' => $tenant->id,
    ]);

    $response = $this->get("/scalerts/id/{$alert->id}");
    $response->assertSuccessful();
});

test('scalert details tab is shown by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tenant = SCTenant::factory()->create();
    $alert = SCAlert::factory()->create([
        'tenantId' => $tenant->id,
    ]);

    $response = $this->get("/scalerts/id/{$alert->id}");

    $response
        ->assertSuccessful()
        ->assertSee('Alert Details')
        ->assertDontSee('Raw JSON Data');
});

test('scalert raw tab can be shown by query parameter', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $tenant = SCTenant::factory()->create();
    $alert = SCAlert::factory()->create([
        'tenantId' => $tenant->id,
    ]);

    $response = $this->get("/scalerts/id/{$alert->id}?tab=raw");

    $response
        ->assertSuccessful()
        ->assertSee('Raw JSON Data')
        ->assertDontSee('Raised at');
});
