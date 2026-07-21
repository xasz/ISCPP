<?php

use App\Models\User;
use Livewire\Volt\Volt;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page for settings 2fa', function () {
    $response = $this->get('/settings/2fa');
    $response->assertRedirect('/login');
});

test('enabling 2fa requires the current password', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Volt::test('settings.2fa')
        ->set('currentPassword', 'wrong-password')
        ->call('enableTwoFactor')
        ->assertHasErrors(['currentPassword']);

    expect($user->fresh()->google2fa_enabled)->toBeFalse();
});

test('enabling 2fa with the correct password shows a qr code and secret', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Volt::test('settings.2fa')
        ->set('currentPassword', 'password')
        ->call('enableTwoFactor')
        ->assertHasNoErrors()
        ->assertSet('showingQrCode', true);

    expect($component->get('secret'))->not->toBeEmpty();
    expect($user->fresh()->google2fa_enabled)->toBeFalse();
});

test('confirming with the correct code enables 2fa', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Volt::test('settings.2fa')
        ->set('currentPassword', 'password')
        ->call('enableTwoFactor');

    $secret = $component->get('secret');
    $code = Google2FA::getCurrentOtp($secret);

    $component->set('verificationCode', $code)
        ->call('confirmTwoFactor')
        ->assertHasNoErrors()
        ->assertSet('showingQrCode', false);

    $user->refresh();
    expect($user->google2fa_enabled)->toBeTrue();
    expect(decrypt($user->google2fa_secret))->toBe($secret);
});

test('confirming with an incorrect code does not enable 2fa', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Volt::test('settings.2fa')
        ->set('currentPassword', 'password')
        ->call('enableTwoFactor');

    $component->set('verificationCode', '000000')
        ->call('confirmTwoFactor')
        ->assertHasErrors(['verificationCode']);

    expect($user->fresh()->google2fa_enabled)->toBeFalse();
});

test('disabling 2fa requires the current password', function () {
    $secret = Google2FA::generateSecretKey();
    $user = User::factory()->create([
        'google2fa_enabled' => true,
        'google2fa_secret' => encrypt($secret),
    ]);
    $this->actingAs($user);

    Volt::test('settings.2fa')
        ->set('currentPassword', 'wrong-password')
        ->call('disableTwoFactor')
        ->assertHasErrors(['currentPassword']);

    expect($user->fresh()->google2fa_enabled)->toBeTrue();
});

test('disabling 2fa with the correct password disables it', function () {
    $secret = Google2FA::generateSecretKey();
    $user = User::factory()->create([
        'google2fa_enabled' => true,
        'google2fa_secret' => encrypt($secret),
    ]);
    $this->actingAs($user);

    Volt::test('settings.2fa')
        ->set('currentPassword', 'password')
        ->call('disableTwoFactor')
        ->assertHasNoErrors();

    $user->refresh();
    expect($user->google2fa_enabled)->toBeFalse();
    expect($user->google2fa_secret)->toBeNull();
});
