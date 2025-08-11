<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ninja_service.windowsSophosCentralEndpointInstallerUrl', '');
        $this->migrator->add('ninja_service.linuxSophosCentralEndpointInstallerUrl', '');
        $this->migrator->add('ninja_service.macSophosCentralEndpointInstallerUrl', '');
        $this->migrator->add('ninja_service.autoPushCentralEndpointInstallerUrl', true);        
    }
};
