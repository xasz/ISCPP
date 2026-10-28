<?php

use App\Models\User;
use App\Settings\SCServiceSettings;

function setSCFeatures(bool $enabled): void
{
    $settings = resolve(SCServiceSettings::class);
    $settings->healthscoresScheduleEnabled = $enabled;
    $settings->alertsScheduleEnabled = $enabled;
    $settings->firewallsScheduleEnabled = $enabled;
    $settings->endpointsScheduleEnabled = $enabled;
    $settings->save();
}

test('the navigation groups central pages by area', function () {
    $this->actingAs(User::factory()->create());
    setSCFeatures(true);

    $this->get(route('sctenants.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Central Tenants', 'Tenants', 'Groups', 'Healthscores',
            'Central Alerts', 'Alerts', 'AutoActions',
            'Central Firewalls', 'Firewalls',
            'Central Endpoints', 'Endpoints',
        ]);
});

test('the navigation hides the areas of disabled features', function () {
    $this->actingAs(User::factory()->create());
    setSCFeatures(false);

    $this->get(route('sctenants.index'))
        ->assertOk()
        ->assertSee('Central Tenants')
        ->assertDontSee('Central Alerts')
        ->assertDontSee('Central Firewalls')
        ->assertDontSee('Central Endpoints');
});
