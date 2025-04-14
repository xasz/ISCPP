<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('halo_service.enabled', false);
        $this->migrator->add('halo_service.instance', '');
        $this->migrator->add('halo_service.url', '');
        $this->migrator->add('halo_service.scope', '');
        $this->migrator->add('halo_service.clientSecret', '');
        $this->migrator->add('halo_service.clientId', '');
        $this->migrator->add('halo_service.token', '');
        $this->migrator->add('halo_service.token_expires_at', 0);
    }
};
