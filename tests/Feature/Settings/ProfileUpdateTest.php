<?php

use App\Models\Event;
use App\Models\User;
use App\Notifications\TestMailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.profile')
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('a test mail is sent to the saved email address', function () {
    Notification::fake();
    config()->set('mail.default', 'smtp');
    $user = User::factory()->create();
    $this->actingAs($user);

    Volt::test('settings.profile')
        ->set('email', 'unsaved@example.com')
        ->call('sendTestMail')
        ->assertSet('testMailStatus', 'success')
        ->assertSee($user->email);

    Notification::assertSentTo($user, TestMailNotification::class);
    expect(Event::where('event', 'mail')->where('type', 'info')->exists())->toBeTrue();
});

test('the test mail warns when the mailer does not deliver mail', function () {
    Notification::fake();
    config()->set('mail.default', 'log');
    $this->actingAs(User::factory()->create());

    Volt::test('settings.profile')
        ->call('sendTestMail')
        ->assertSet('testMailStatus', 'warning')
        ->assertSee('This mailer does not deliver mail');
});

test('a failing test mail shows the error and logs it', function () {
    config()->set('mail.default', 'missing-mailer');
    $this->actingAs(User::factory()->create());

    Volt::test('settings.profile')
        ->call('sendTestMail')
        ->assertSet('testMailStatus', 'error')
        ->assertSee('The test mail could not be sent');

    expect(Event::where('event', 'mail')->where('type', 'error')->exists())->toBeTrue();
});

test('test mails are rate limited', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->actingAs($user);

    $component = Volt::test('settings.profile');
    foreach (range(1, 3) as $attempt) {
        $component->call('sendTestMail');
    }

    $component->call('sendTestMail')
        ->assertSet('testMailStatus', 'error')
        ->assertSee('Too many test mails');

    Notification::assertSentToTimes($user, TestMailNotification::class, 3);
});
