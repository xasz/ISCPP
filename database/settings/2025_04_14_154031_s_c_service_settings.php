<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sc_service.enabled', false);
        $this->migrator->add('sc_service.partnerId', '');
        $this->migrator->add('sc_service.clientSecret', '');
        $this->migrator->add('sc_service.clientId', '');
        $this->migrator->add('sc_service.token', '');
        $this->migrator->add('sc_service.refresh_token', '');
        $this->migrator->add('sc_service.token_expires_at', 0);
    }
};
