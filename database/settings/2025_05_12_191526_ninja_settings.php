<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ninja_service.enabled', false);
        $this->migrator->add('ninja_service.instance', '');
        $this->migrator->add('ninja_service.scope', '');
        $this->migrator->add('ninja_service.clientSecret', '');
        $this->migrator->add('ninja_service.clientId', '');
        $this->migrator->add('ninja_service.token', '');
        $this->migrator->add('ninja_service.token_expires_at', 0);
    }
};
