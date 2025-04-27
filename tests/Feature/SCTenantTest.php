<?php

use App\Models\User;
use App\Models\SCTenant;

test('guests are redirected to the login page for sctenants', function () {
    $response = $this->get('/sctenants');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the sctenants index page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/sctenants');
    $response->assertStatus(200);
});

test('authenticated users can view a specific sctenant', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $tenant = SCTenant::factory()->create();

    $response = $this->get("/sctenants/{$tenant->id}");
    $response->assertStatus(200);
});