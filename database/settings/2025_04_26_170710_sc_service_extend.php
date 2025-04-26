<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sc_service.alertsScheduleEnabled', true);
        $this->migrator->add('sc_service.endpointsScheduleEnabled', false);
        $this->migrator->add('sc_service.firewallsScheduleEnabled', false);

        $this->migrator->add('sc_service.lastAlertsSchedule', null);
        $this->migrator->add('sc_service.lastEndpointsSchedule', null);
        $this->migrator->add('sc_service.lastFirewallsSchedule', null);
    }
};
