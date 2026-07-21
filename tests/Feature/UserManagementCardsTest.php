<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('creating an invitation requires a name and a unique email', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    Volt::test('usermanagement.card-invites')
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->call('createInvitation')
        ->assertHasErrors(['name', 'email']);

    Volt::test('usermanagement.card-invites')
        ->set('name', 'Duplicate')
        ->set('email', $admin->email)
        ->call('createInvitation')
        ->assertHasErrors(['email' => 'unique']);
});

test('creating an invitation creates an unverified user and a shareable link', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $component = Volt::test('usermanagement.card-invites')
        ->set('name', 'New Person')
        ->set('email', 'new-person@example.com')
        ->call('createInvitation')
        ->assertHasNoErrors()
        ->assertSet('showLink', true);

    $invited = User::where('email', 'new-person@example.com')->first();

    expect($invited)->not->toBeNull();
    expect($invited->email_verified_at)->toBeNull();

    $tokenRow = DB::table('password_reset_tokens')->where('email', $invited->email)->first();
    expect($tokenRow)->not->toBeNull();

    expect($component->get('invitationLink'))
        ->toContain(url('invitation'))
        ->toContain(urlencode($invited->email));
});

test('regenerating a link replaces the stored token', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $invited = User::factory()->unverified()->create(['email' => 'pending@example.com']);
    DB::table('password_reset_tokens')->insert([
        'email' => $invited->email,
        'token' => bcrypt('old-token'),
        'created_at' => now(),
    ]);

    Volt::test('usermanagement.card-invites')
        ->call('regenerateLink', $invited->id)
        ->assertSet('showLink', true);

    $tokenRow = DB::table('password_reset_tokens')->where('email', $invited->email)->first();

    expect(Hash::check('old-token', $tokenRow->token))->toBeFalse();
});

test('deleting an invitation removes the user and its token', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $invited = User::factory()->unverified()->create(['email' => 'pending@example.com']);
    DB::table('password_reset_tokens')->insert([
        'email' => $invited->email,
        'token' => bcrypt('some-token'),
        'created_at' => now(),
    ]);

    Volt::test('usermanagement.card-invites')
        ->call('deleteInvitation', $invited->id);

    expect(User::find($invited->id))->toBeNull();
    expect(DB::table('password_reset_tokens')->where('email', $invited->email)->exists())->toBeFalse();
});

test('the user manager lists all users', function () {
    $admin = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($admin);

    Volt::test('usermanagement.card-manager')
        ->assertSee($admin->email)
        ->assertSee($other->email);
});

test('showing details loads the selected user', function () {
    $admin = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($admin);

    $component = Volt::test('usermanagement.card-manager')
        ->call('showDetails', $other->id);

    expect($component->get('userDetails')->id)->toBe($other->id);

    $component->call('closeModal');

    expect($component->get('userDetails'))->toBeNull();
});

test('deleting a user removes them', function () {
    $admin = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($admin);

    Volt::test('usermanagement.card-manager')
        ->call('showDetails', $other->id)
        ->call('deleteUser', $other->id);

    expect(User::find($other->id))->toBeNull();
});
