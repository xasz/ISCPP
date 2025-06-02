<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->deleteIfExists('sc_service.token_expires_at');
        $this->migrator->add('sc_service.token_expires_at', 0);

        $this->migrator->deleteIfExists('halo_service.token_expires_at');
        $this->migrator->add('halo_service.token_expires_at', 0);
        
        $this->migrator->deleteIfExists('ninja_service.token_expires_at');
        $this->migrator->add('ninja_service.token_expires_at', 0);
    }
};
