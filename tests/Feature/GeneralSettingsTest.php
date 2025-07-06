<?php

use App\Models\User;
use App\Models\SCAlert;
use App\Models\SCTenant;

test('guests are redirected to the login page for general settings', function () {
    $response = $this->get('/generalsettings');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the general settings page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/generalsettings');
    $response->assertStatus(200);
});