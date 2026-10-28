<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sc_service.firewallBackupsScheduleEnabled', false);
        $this->migrator->add('sc_service.firewallBackupsRetention', 7);
        $this->migrator->add('sc_service.lastFirewallBackupsSchedule', null);
    }
};
