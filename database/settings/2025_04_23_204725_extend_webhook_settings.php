<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('webhook.basicAuthEnabled', false);
        $this->migrator->add('webhook.basicAuthUsername', '');
        $this->migrator->add('webhook.basicAuthPassword', '');
    }
};
