<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt as LivewireVolt;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function createTwoFactorUser(): array
{
    $secret = Google2FA::generateSecretKey();

    $user = User::factory()->create([
        'google2fa_enabled' => true,
        'google2fa_secret' => encrypt($secret),
    ]);

    return [$user, $secret];
}

test('users without 2fa enabled are not challenged', function () {
    $user = User::factory()->create(['google2fa_enabled' => false]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
});

test('users with 2fa enabled are redirected to the verification page', function () {
    [$user] = createTwoFactorUser();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('2fa.verify'));
});

test('users with 2fa enabled can access protected routes once verified in session', function () {
    [$user] = createTwoFactorUser();

    $response = $this->actingAs($user)
        ->withSession(['google2fa_verified' => true])
        ->get('/dashboard');

    $response->assertOk();
});

test('a correct code verifies 2fa and grants access', function () {
    [$user, $secret] = createTwoFactorUser();
    $this->actingAs($user);

    $code = Google2FA::getCurrentOtp($secret);

    LivewireVolt::test('2fa-verify')
        ->set('code', $code)
        ->call('verify')
        ->assertHasNoErrors();

    expect(session('google2fa_verified'))->toBeTrue();
});

test('an incorrect code is rejected and does not grant access', function () {
    [$user] = createTwoFactorUser();
    $this->actingAs($user);

    LivewireVolt::test('2fa-verify')
        ->set('code', '000000')
        ->call('verify')
        ->assertHasErrors(['code']);

    expect(session('google2fa_verified'))->not->toBeTrue();
});

test('repeated incorrect codes are rate limited', function () {
    [$user] = createTwoFactorUser();
    $this->actingAs($user);

    $component = LivewireVolt::test('2fa-verify');

    for ($i = 0; $i < 5; $i++) {
        $component->set('code', '000000')->call('verify');
    }

    // The 6th attempt should be blocked by the rate limiter, not by a plain "invalid code" error.
    $component->set('code', '000000')->call('verify');

    $component->assertHasErrors(['code']);
    expect(session('google2fa_verified'))->not->toBeTrue();
});

test('a successful verification clears the rate limiter', function () {
    [$user, $secret] = createTwoFactorUser();
    $this->actingAs($user);

    $component = LivewireVolt::test('2fa-verify');
    $component->set('code', '000000')->call('verify');

    $code = Google2FA::getCurrentOtp($secret);
    $component->set('code', $code)->call('verify')->assertHasNoErrors();

    expect(RateLimiter::tooManyAttempts('google2fa|'.$user->id.'|127.0.0.1', 5))->toBeFalse();
});
