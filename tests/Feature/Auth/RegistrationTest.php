<?php

use App\Models\User;
use Livewire\Volt\Volt as LivewireVolt;


uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);
});

test('new users can register once', function () {

    $response = LivewireVolt::test('auth.register')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register');
    
    $response
        ->assertHasNoErrors();

    $response = LivewireVolt::test('auth.login')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->call('login');

    $response
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $response = $this->get('/register');
    $response->assertStatus(302);
});
