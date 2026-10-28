<?php

use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Models\SCTenantGroup;
use App\Models\SCTenantHealthscore;
use App\Models\SCTenantHealthscoreThreshold;
use App\Models\User;
use Livewire\Volt\Volt;

test('guests are redirected from the ISCPP event pages', function (string $path) {
    $this->get($path)->assertRedirect('/login');
})->with(['/iscpp/events', '/iscpp/rules', '/iscpp/rules/create', '/iscpp/info']);

test('authenticated users can open the ISCPP event pages', function (string $path) {
    $this->actingAs(User::factory()->create());

    $this->get($path)->assertStatus(200);
})->with(['/iscpp/events', '/iscpp/rules', '/iscpp/rules/create', '/iscpp/info']);

test('the event info tree lists every event group and opens on the overview', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.event-info')
        ->assertSee('Sophos Central')
        ->assertSee('Healthscore')
        ->assertSee('Threshold')
        ->assertSee('Payload envelope')
        ->assertDontSee('data.severity');
});

test('clicking an event in the tree shows its details', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.event-info')
        ->call('select', 'sc.alert.created')
        ->assertSet('selected', 'sc.alert.created')
        ->assertSee('SC Alert Created')
        ->assertSee('data.severity')
        ->assertSee('warning')
        ->assertDontSee('Payload envelope');
});

test('the selected event is read from the url', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/iscpp/info?event=healthscore.threshold.dropped')
        ->assertStatus(200)
        ->assertSee('Healthscore Threshold Dropped')
        ->assertSee('data.scoreKey');
});

test('an unknown event key falls back to the overview', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.event-info')
        ->call('select', 'does.not.exist')
        ->assertSet('selected', '')
        ->assertSee('Payload envelope');
});

test('the rules page lists the event, tenant and endpoint', function () {
    $this->actingAs(User::factory()->create());
    ISCPPEventRule::factory()->forEvent('sc.alert.created')->create([
        'name' => 'Alert forwarder',
        'url' => 'https://hooks.example.com/psa',
    ]);

    $this->get('/iscpp/rules')
        ->assertStatus(200)
        ->assertSee('Alert forwarder')
        ->assertSee('SC Alert Created')
        ->assertSee('All tenants')
        ->assertSee('hooks.example.com/psa');
});

test('an existing rule can be opened in the edit form', function () {
    $this->actingAs(User::factory()->create());
    $rule = ISCPPEventRule::factory()->forEvent('sc.alert.created')->create(['name' => 'Alerts only']);

    $this->get('/iscpp/rules/'.$rule->id.'/edit')
        ->assertStatus(200)
        ->assertSee('Alerts only');
});

test('the rule form shows an example payload for the selected event', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.rule-form')
        ->set('eventKey', 'sc.alert.created')
        ->assertSee('payloadVersion')
        // exampleData() on the event class, so the example is real before anything fires.
        ->assertSee('Malware detected');
});

test('the example payload uses a real recorded event when one exists', function () {
    $this->actingAs(User::factory()->create());
    $event = ISCPPEventLog::factory()->create(['key' => 'sc.tenant.created']);
    $payload = $event->payload;
    $payload['data'] = ['name' => 'Recorded Tenant Name'];
    $event->update(['payload' => $payload]);

    Volt::test('iscpp.rule-form')
        ->set('eventKey', 'sc.tenant.created')
        ->assertSee('Recorded Tenant Name');
});

test('the event log lists recorded events', function () {
    $this->actingAs(User::factory()->create());
    ISCPPEventLog::factory()->create();

    $this->get('/iscpp/events')->assertStatus(200)->assertSee('SC Tenant Created');
});

test('the event log can be filtered by key', function () {
    $this->actingAs(User::factory()->create());
    ISCPPEventLog::factory()->create(['key' => 'sc.tenant.created']);

    $this->get('/iscpp/events?key=healthscore.threshold.dropped')
        ->assertStatus(200)
        ->assertSee('No ISCPP events recorded yet.');
});

test('the event detail page shows the frozen payload', function () {
    $this->actingAs(User::factory()->create());
    $event = ISCPPEventLog::factory()->create();

    $this->get('/iscpp/events/'.$event->id)
        ->assertStatus(200)
        ->assertSee('payloadVersion');
});

test('a rule can be created through the form', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    Volt::test('iscpp.rule-form')
        ->set('name', 'High severity alerts')
        ->set('eventKey', 'sc.alert.created')
        ->set('tenantScope', 'tenant')
        ->set('tenantId', $tenant->id)
        ->call('addCondition')
        ->set('conditions.0.path', 'data.severity')
        ->set('conditions.0.operator', 'in')
        ->set('conditions.0.value', 'high, medium')
        ->set('url', 'https://hooks.example.com/psa')
        ->set('basicAuthUsername', 'iscpp')
        ->set('basicAuthPassword', 'pw')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('iscpp.rules.index'));

    $rule = ISCPPEventRule::with('conditions')->where('name', 'High severity alerts')->first();

    expect($rule)->not->toBeNull()
        ->and($rule->event_key)->toBe('sc.alert.created')
        ->and($rule->tenantId)->toBe($tenant->id)
        ->and($rule->conditions->first()->value)->toBe(['high', 'medium'])
        ->and($rule->url)->toBe('https://hooks.example.com/psa')
        ->and($rule->basic_auth_password)->toBe('pw');
});

test('a rule with no tenant selected applies to all tenants', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.rule-form')
        ->set('name', 'Everything')
        ->set('eventKey', 'sc.tenant.created')
        ->set('tenantId', '')
        ->set('url', 'https://hooks.example.com/all')
        ->call('save')
        ->assertHasNoErrors();

    expect(ISCPPEventRule::where('name', 'Everything')->value('tenantId'))->toBeNull();
});

test('a rule can be scoped to a tenant group through the form', function () {
    $this->actingAs(User::factory()->create());
    $group = SCTenantGroup::factory()->create();

    Volt::test('iscpp.rule-form')
        ->set('name', 'Group rule')
        ->set('eventKey', 'sc.tenant.created')
        // A tenant chosen before switching scope must not be stored alongside the group.
        ->set('tenantScope', 'tenant')
        ->set('tenantId', SCTenant::factory()->create()->id)
        ->set('tenantScope', 'group')
        ->set('tenantGroupId', (string) $group->id)
        ->set('url', 'https://hooks.example.com/group')
        ->call('save')
        ->assertHasNoErrors();

    $rule = ISCPPEventRule::where('name', 'Group rule')->sole();

    expect($rule->sc_tenant_group_id)->toBe($group->id)
        ->and($rule->tenantId)->toBeNull();

    Volt::test('iscpp.rule-form', ['ruleId' => $rule->id])
        ->assertSet('tenantScope', 'group')
        ->assertSet('tenantGroupId', (string) $group->id);
});

test('a group scoped rule requires a group', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.rule-form')
        ->set('name', 'Group rule')
        ->set('eventKey', 'sc.tenant.created')
        ->set('tenantScope', 'group')
        ->set('url', 'https://hooks.example.com/group')
        ->call('save')
        ->assertHasErrors('tenantGroupId');
});

test('the rules list shows the tenant group of a rule', function () {
    $this->actingAs(User::factory()->create());
    $group = SCTenantGroup::factory()->create(['name' => 'Key Accounts']);
    ISCPPEventRule::factory()->forTenantGroup($group)->create();

    $this->get(route('iscpp.rules.index'))
        ->assertOk()
        ->assertSee('Key Accounts');
});

test('a rule pointing at a private address is rejected', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('iscpp.rule-form')
        ->set('name', 'Internal')
        ->set('eventKey', 'sc.tenant.created')
        ->set('url', 'http://127.0.0.1/hook')
        ->call('save')
        ->assertHasErrors('url');

    expect(ISCPPEventRule::count())->toBe(0);
});

test('editing a rule keeps the stored password when the field is left blank', function () {
    $this->actingAs(User::factory()->create());
    $rule = ISCPPEventRule::factory()->create(['basic_auth_password' => 'original-pw']);

    Volt::test('iscpp.rule-form', ['ruleId' => $rule->id])
        ->set('name', 'Renamed')
        ->call('save')
        ->assertHasNoErrors();

    expect($rule->fresh()->basic_auth_password)->toBe('original-pw')
        ->and($rule->fresh()->name)->toBe('Renamed');
});

test('an existing rule loads its settings back into the form', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();
    $rule = ISCPPEventRule::factory()
        ->forEvent('sc.alert.created')
        ->forTenant($tenant->id)
        ->create(['url' => 'https://hooks.example.com/x', 'basic_auth_password' => 'pw']);
    $rule->conditions()->create(['path' => 'data.severity', 'operator' => 'eq', 'value' => ['high']]);

    Volt::test('iscpp.rule-form', ['ruleId' => $rule->id])
        ->assertSet('eventKey', 'sc.alert.created')
        ->assertSet('tenantId', $tenant->id)
        ->assertSet('conditions.0.path', 'data.severity')
        ->assertSet('conditions.0.value', 'high')
        ->assertSet('url', 'https://hooks.example.com/x')
        // The stored password is never sent back to the browser.
        ->assertSet('basicAuthPassword', '')
        ->assertSet('hasStoredPassword', true);
});

test('a rule can be disabled and deleted from the table', function () {
    $this->actingAs(User::factory()->create());
    $rule = ISCPPEventRule::factory()->create();

    Volt::test('iscpp.rules-table')->call('toggle', $rule->id);
    expect($rule->fresh()->enabled)->toBeFalse();

    Volt::test('iscpp.rules-table')->call('delete', $rule->id);
    expect(ISCPPEventRule::find($rule->id))->toBeNull();
});

test('re-enabling a rule from the table clears the circuit breaker', function () {
    $this->actingAs(User::factory()->create());
    $rule = ISCPPEventRule::factory()->disabled()->create(['consecutive_failures' => 10]);

    Volt::test('iscpp.rules-table')->call('toggle', $rule->id);

    expect($rule->fresh()->enabled)->toBeTrue()
        ->and($rule->fresh()->consecutive_failures)->toBe(0)
        ->and($rule->fresh()->disabled_at)->toBeNull();
});

test('the healthscore threshold card is reachable on the tenant healthscore tab', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    $this->get(route('sctenants.tenantHealthscore', $tenant))
        ->assertStatus(200)
        ->assertSee('Healthscore Thresholds');
});

test('the healthscore threshold card explains the silent baseline', function () {
    $this->actingAs(User::factory()->create());

    Volt::test('sctenant.card-healthscore-thresholds', ['sctenant' => SCTenant::factory()->create()])
        ->assertSee('no ISCPP events are raised and no webhooks are sent');
});

test('the healthscore threshold card saves per tenant overrides', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    // The factory scores are random, and a threshold below the current score is raised
    // to it on save. Pin the score below the override so the stored value is the input.
    $healthscore = $tenant->SCTenantHealthscore;
    $raw = $healthscore->rawData;
    data_set($raw, 'endpoint.protection.computer.score', 40);
    $healthscore->update(['rawData' => $raw]);

    // Index 0 is the first entry of AvailableScores(): protection.computer.score.
    Volt::test('sctenant.card-healthscore-thresholds', ['sctenant' => $tenant])
        ->set('thresholds.0.threshold', 60)
        ->call('save')
        ->assertHasNoErrors();

    expect(SCTenantHealthscoreThreshold::where('tenantId', $tenant->id)
        ->where('score_key', 'protection.computer.score')
        ->value('threshold'))->toBe(60);
});

test('the healthscores overview marks scores below their threshold', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    $healthscore = $tenant->SCTenantHealthscore;
    $raw = $healthscore->rawData;
    data_set($raw, 'endpoint.protection.computer.score', 40);
    $healthscore->update(['rawData' => $raw]);

    // The row already exists: saving the healthscore evaluated every score.
    SCTenantHealthscoreThreshold::updateOrCreate(
        ['tenantId' => $tenant->id, 'score_key' => 'protection.computer.score'],
        ['threshold' => 80],
    );

    $this->get(route('sctenants.healthscores'))
        ->assertStatus(200)
        ->assertSee('Below threshold of 80');
});

test('the healthscores overview keeps the full tenant name as tooltip for truncated names', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create(['name' => 'A Very Long Tenant Name That Would Otherwise Stretch The Table GmbH']);

    $this->get(route('sctenants.healthscores'))
        ->assertStatus(200)
        ->assertSee('title="A Very Long Tenant Name That Would Otherwise Stretch The Table GmbH"', false);
});

test('the healthscore groups cover every available score', function () {
    $groupedScores = collect(SCTenantHealthscore::ScoreGroups())
        ->flatMap(fn (array $groupScores) => array_keys($groupScores))
        ->sort()
        ->values()
        ->all();

    expect($groupedScores)->toBe(collect(SCTenantHealthscore::AvailableScores())->sort()->values()->all());
});

test('the healthscores overview shows grouped score columns', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('sctenants.healthscores'))
        ->assertStatus(200)
        ->assertSeeInOrder([
            'colspan="2"', 'Protection',
            'colspan="2"', 'Policy',
            'colspan="3"', 'Exclusions',
            'colspan="3"', 'Tamper Protection',
            'colspan="2"', 'MDR',
            'title="Protection – Computer"',
            'title="MDR – Contact"',
        ], false);
});

test('the healthscores overview can show only tenants below a threshold', function () {
    $this->actingAs(User::factory()->create());

    $belowTenant = SCTenant::factory()->create(['name' => 'Tenant Below Threshold']);
    $okTenant = SCTenant::factory()->create(['name' => 'Tenant Within Threshold']);

    $healthscore = $belowTenant->SCTenantHealthscore;
    $raw = $healthscore->rawData;
    data_set($raw, 'endpoint.protection.computer.score', 40);
    $healthscore->update(['rawData' => $raw]);

    SCTenantHealthscoreThreshold::updateOrCreate(
        ['tenantId' => $belowTenant->id, 'score_key' => 'protection.computer.score'],
        ['threshold' => 80],
    );

    $okHealthscore = $okTenant->SCTenantHealthscore;
    $okRaw = $okHealthscore->rawData;
    foreach (SCTenantHealthscore::AvailableScores() as $scoreKey) {
        data_set($okRaw, 'endpoint.'.$scoreKey, 100);
    }
    $okHealthscore->update(['rawData' => $okRaw]);

    $this->get(route('sctenants.healthscores', ['belowThreshold' => 1]))
        ->assertStatus(200)
        ->assertSee('Tenant Below Threshold')
        ->assertDontSee('Tenant Within Threshold');

    $this->get(route('sctenants.healthscores'))
        ->assertSee('Tenant Below Threshold')
        ->assertSee('Tenant Within Threshold');
});

test('the healthscores overview leaves scores above their threshold unmarked', function () {
    $this->actingAs(User::factory()->create());
    $tenant = SCTenant::factory()->create();

    $healthscore = $tenant->SCTenantHealthscore;
    $raw = $healthscore->rawData;

    foreach (SCTenantHealthscore::AvailableScores() as $scoreKey) {
        data_set($raw, 'endpoint.'.$scoreKey, 100);
    }

    $healthscore->update(['rawData' => $raw]);

    $this->get(route('sctenants.healthscores'))
        ->assertStatus(200)
        ->assertDontSee('Below threshold of');
});
