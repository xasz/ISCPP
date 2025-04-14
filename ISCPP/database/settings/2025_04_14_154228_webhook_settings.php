<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('webhook.enabled', false);
        $this->migrator->add('webhook.url', '');
        $this->migrator->add('webhook.payload', '');
        $this->migrator->add('webhook.header', '');
    }
};
