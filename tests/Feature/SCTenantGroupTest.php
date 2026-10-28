<?php

use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Models\SCTenantGroup;
use App\Models\User;
use Livewire\Volt\Volt;

test('guests are redirected to the login page for tenant groups', function () {
    $this->get(route('sctenantgroups.index'))->assertRedirect('/login');
});

test('the tenant groups list shows each group with its member count', function () {
    $this->actingAs(User::factory()->create());
    SCTenantGroup::factory()->withTenants(SCTenant::factory()->count(2)->create())->create(['name' => 'Key Accounts']);

    $this->get(route('sctenantgroups.index'))
        ->assertOk()
        ->assertSee('Key Accounts');
});

test('a tenant group can be created with members', function () {
    $this->actingAs(User::factory()->create());
    $tenants = SCTenant::factory()->count(2)->create();

    Volt::test('sctenantgroups.group-form')
        ->set('name', 'Key Accounts')
        ->set('description', 'Customers with an SLA')
        ->set('tenantIds', $tenants->pluck('id')->all())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('sctenantgroups.index'));

    $group = SCTenantGroup::where('name', 'Key Accounts')->sole();

    expect($group->description)->toBe('Customers with an SLA')
        ->and($group->tenants->pluck('id')->sort()->values()->all())
        ->toBe($tenants->pluck('id')->sort()->values()->all());
});

test('editing a tenant group replaces its members', function () {
    $this->actingAs(User::factory()->create());
    [$removed, $kept, $added] = SCTenant::factory()->count(3)->create();
    $group = SCTenantGroup::factory()->withTenants([$removed, $kept])->create();

    Volt::test('sctenantgroups.group-form', ['groupId' => $group->id])
        ->assertSet('tenantIds', fn (array $ids) => collect($ids)->sort()->values()->all() === collect([$removed->id, $kept->id])->sort()->values()->all())
        ->set('tenantIds', [$kept->id, $added->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($group->fresh()->tenants->pluck('id')->sort()->values()->all())
        ->toBe(collect([$kept->id, $added->id])->sort()->values()->all());
});

test('tenant group names must be unique', function () {
    $this->actingAs(User::factory()->create());
    SCTenantGroup::factory()->create(['name' => 'Key Accounts']);

    Volt::test('sctenantgroups.group-form')
        ->set('name', 'Key Accounts')
        ->call('save')
        ->assertHasErrors('name');
});

test('the tenant search filters the member list', function () {
    $this->actingAs(User::factory()->create());
    SCTenant::factory()->create(['name' => 'Alpha Industries']);
    SCTenant::factory()->create(['name' => 'Beta Logistics']);

    Volt::test('sctenantgroups.group-form')
        ->set('search', 'alpha')
        ->assertSee('Alpha Industries')
        ->assertDontSee('Beta Logistics');
});

test('the tenant iscpp tab lists the groups of the tenant', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();
    SCTenantGroup::factory()->withTenants([$tenant])->create(['name' => 'Key Accounts']);
    SCTenantGroup::factory()->create(['name' => 'Other Group']);

    $this->get(route('sctenants.tenantISCPPSettings', $tenant))
        ->assertOk()
        ->assertSee('Key Accounts')
        ->assertDontSee('Other Group');
});

test('the tenant iscpp tab says when a tenant has no groups', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    $this->get(route('sctenants.tenantISCPPSettings', $tenant))
        ->assertOk()
        ->assertSee('This tenant is not a member of any group.');
});

test('a tenant group can be deleted', function () {
    $this->actingAs(User::factory()->create());
    $group = SCTenantGroup::factory()->withTenants([SCTenant::factory()->create()])->create();

    Volt::test('sctenantgroups.groups-table')->call('delete', $group->id);

    expect(SCTenantGroup::count())->toBe(0);
});

test('a tenant group used by a rule cannot be deleted', function () {
    $this->actingAs(User::factory()->create());
    $group = SCTenantGroup::factory()->create();
    ISCPPEventRule::factory()->forTenantGroup($group)->create();

    Volt::test('sctenantgroups.groups-table')
        ->call('delete', $group->id)
        ->assertSee('This group is used by 1 rule(s)');

    expect(SCTenantGroup::count())->toBe(1);
});
