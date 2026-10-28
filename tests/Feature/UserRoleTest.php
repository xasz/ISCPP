<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Volt\Volt;

dataset('restricted pages', [
    'usermanagement.index',
    'generalsettings.sc',
    'generalsettings.webhookAlerts',
    'generalsettings.halo',
    'generalsettings.ninja',
    'generalsettings.commands',
]);

dataset('restricted components', [
    'usermanagement.card-manager',
    'usermanagement.card-invites',
    'settings.card-sc',
    'settings.card-sc-features',
    'settings.card-webhook-alert',
    'settings.card-haloServiceSettings',
    'settings.card-ninjaServiceSettings',
    'settings.card-commands',
]);

test('editors cannot open user management or general settings', function (string $routeName) {
    $this->actingAs(User::factory()->create());

    $this->get(route($routeName))->assertForbidden();
})->with('restricted pages');

test('superadmins can open user management and general settings', function (string $routeName) {
    $this->actingAs(User::factory()->superAdmin()->create());

    $this->get(route($routeName))->assertOk();
})->with('restricted pages');

test('editors cannot use the restricted components directly', function (string $component) {
    $this->actingAs(User::factory()->create());

    Volt::test($component)->assertForbidden();
})->with('restricted components');

test('editors can use the rest of the application', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('sctenants.index'))->assertOk();
    $this->get(route('iscpp.rules.index'))->assertOk();
    $this->get(route('settings.profile'))->assertOk();
});

test('the navigation shows user management and general settings only to superadmins', function () {
    $this->actingAs(User::factory()->create());
    $this->get(route('dashboard'))
        ->assertDontSee('User Management')
        ->assertDontSee('General Settings');

    $this->actingAs(User::factory()->superAdmin()->create());
    $this->get(route('dashboard'))
        ->assertSee('User Management')
        ->assertSee('General Settings');
});

test('an invitation stores the chosen role and defaults to editor', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    Volt::test('usermanagement.card-invites')
        ->assertSet('role', 'editor')
        ->set('name', 'Default Person')
        ->set('email', 'default@example.com')
        ->call('createInvitation')
        ->set('name', 'Admin Person')
        ->set('email', 'admin@example.com')
        ->set('role', 'superadmin')
        ->call('createInvitation')
        ->assertHasNoErrors();

    expect(User::where('email', 'default@example.com')->value('role'))->toBe(UserRole::Editor)
        ->and(User::where('email', 'admin@example.com')->value('role'))->toBe(UserRole::SuperAdmin);
});

test('a superadmin can change the role of another user', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    $editor = User::factory()->create();

    Volt::test('usermanagement.card-manager')
        ->call('changeRole', $editor->id, 'superadmin')
        ->assertSee('is now Superadmin');

    expect($editor->fresh()->role)->toBe(UserRole::SuperAdmin);
});

test('a superadmin cannot change their own role', function () {
    $admin = User::factory()->superAdmin()->create();
    User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Volt::test('usermanagement.card-manager')
        ->call('changeRole', $admin->id, 'editor')
        ->assertSee('You cannot change your own role.');

    expect($admin->fresh()->role)->toBe(UserRole::SuperAdmin);
});

test('a superadmin is only the last one while no other superadmin exists', function () {
    $first = User::factory()->superAdmin()->create();
    User::factory()->create();

    expect($first->isLastSuperAdmin())->toBeTrue();

    User::factory()->superAdmin()->create();

    expect($first->isLastSuperAdmin())->toBeFalse();
});

test('a demoted superadmin loses access immediately', function () {
    $actingSuperAdmin = User::factory()->superAdmin()->create();
    $otherSuperAdmin = User::factory()->superAdmin()->create();
    $this->actingAs($actingSuperAdmin);

    Volt::test('usermanagement.card-manager')->call('changeRole', $otherSuperAdmin->id, 'editor');

    $this->actingAs($otherSuperAdmin->fresh());
    $this->get(route('usermanagement.index'))->assertForbidden();
});

test('a superadmin cannot delete themselves', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);

    Volt::test('usermanagement.card-manager')
        ->call('deleteUser', $admin->id)
        ->assertSee('You cannot delete yourself.');

    expect(User::whereKey($admin->id)->exists())->toBeTrue();
});

test('the first registered account becomes superadmin', function () {
    Volt::test('auth.register')
        ->set('name', 'First User')
        ->set('email', 'first@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasNoErrors();

    expect(User::where('email', 'first@example.com')->value('role'))->toBe(UserRole::SuperAdmin);
});

test('registration is refused once an account exists', function () {
    User::factory()->create();

    Volt::test('auth.register')
        ->set('name', 'Second User')
        ->set('email', 'second@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertForbidden();

    expect(User::where('email', 'second@example.com')->exists())->toBeFalse();
});

test('the migration keeps existing users as superadmins', function () {
    $migration = require database_path('migrations/2026_10_28_143857_add_role_to_users_table.php');
    $migration->down();
    expect(Schema::hasColumn('users', 'role'))->toBeFalse();

    DB::table('users')->insert([
        'name' => 'Existing', 'email' => 'existing@example.com', 'password' => 'x',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $migration->up();

    expect(DB::table('users')->where('email', 'existing@example.com')->value('role'))->toBe('superadmin');
});
