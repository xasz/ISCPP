<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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

test('a user can accept a valid invitation and set their password', function () {
    $invited = User::factory()->unverified()->create(['email' => 'invitee@example.com']);

    $token = 'a-valid-token';
    DB::table('password_reset_tokens')->insert([
        'email' => $invited->email,
        'token' => bcrypt($token),
        'created_at' => now(),
    ]);

    $showResponse = $this->get("/invitation/{$token}?email=" . urlencode($invited->email));
    $showResponse->assertOk();

    $acceptResponse = $this->post('/invitation', [
        'token' => $token,
        'email' => $invited->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $acceptResponse->assertRedirect(route('login'));

    $invited->refresh();
    expect($invited->email_verified_at)->not->toBeNull();
    expect(Hash::check('new-password', $invited->password))->toBeTrue();
    expect(DB::table('password_reset_tokens')->where('email', $invited->email)->exists())->toBeFalse();
});

test('an invitation cannot be accepted with the wrong token', function () {
    $invited = User::factory()->unverified()->create(['email' => 'invitee@example.com']);

    DB::table('password_reset_tokens')->insert([
        'email' => $invited->email,
        'token' => bcrypt('correct-token'),
        'created_at' => now(),
    ]);

    $response = $this->post('/invitation', [
        'token' => 'wrong-token',
        'email' => $invited->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors('email');
    expect($invited->fresh()->email_verified_at)->toBeNull();
});

test('invitation processing is rate limited', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'throttle-test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'token' => 'test-token',
    ];

    for ($i = 0; $i < 10; $i++) {
        $this->post('/invitation', $data);
    }

    $response = $this->post('/invitation', $data);

    $response->assertStatus(429);
});