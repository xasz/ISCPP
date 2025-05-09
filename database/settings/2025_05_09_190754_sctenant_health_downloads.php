<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sc_service.downloadsScheduleEnabled', true);
        $this->migrator->add('sc_service.healthscoresScheduleEnabled', true);
        
        $this->migrator->add('sc_service.lastDownloadsSchedule', null);
        $this->migrator->add('sc_service.lastHealthscoresSchedule', null);
    }
};
