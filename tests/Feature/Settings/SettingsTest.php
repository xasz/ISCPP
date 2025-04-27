<?php

use App\Models\User;

test('guests are redirected to the login page for settings profile', function () {
    $response = $this->get('/settings/profile');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the settings profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/settings/profile');
    $response->assertStatus(200);
});

test('guests are redirected to the login page for settings password', function () {
    $response = $this->get('/settings/password');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the settings password page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/settings/password');
    $response->assertStatus(200);
});

test('guests are redirected to the login page for settings 2fa', function () {
    $response = $this->get('/settings/2fa');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the settings 2fa page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/settings/2fa');
    $response->assertStatus(200);
});

test('guests are redirected to the login page for settings appearance', function () {
    $response = $this->get('/settings/appearance');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the settings appearance page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/settings/appearance');
    $response->assertStatus(200);
});