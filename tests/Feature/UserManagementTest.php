<?php

use App\Models\User;

test('guests are redirected to the login page for user management', function () {
    $response = $this->get('/usermanagement');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the user management page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/usermanagement');
    $response->assertStatus(200);
});

test('users can access invitation acceptance page', function () {
    $token = 'test-token';
    
    $response = $this->get("/invitation/{$token}");
    $response->assertStatus(200);
});

test('users can process invitation', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => 'test-token'
    ];
    
    $response = $this->post('/invitation', $data);
    $response->assertStatus(302); // Assuming successful redirect after submission
});