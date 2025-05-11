<?php

namespace App\Settings;

use Carbon\Carbon;
use Spatie\LaravelSettings\Settings;

class SCServiceSettings extends Settings
{
    public ?string $clientSecret = '';
    public ?string $clientId = '';
    public ?string $partnerId = null;


    public ?string $token = null;
    public ?string $refresh_token = null;
    public ?string $token_expires_at = null;

    public ?bool $alertsScheduleEnabled = true;
    public ?bool $endpointsScheduleEnabled = false;
    public ?bool $firewallsScheduleEnabled = false;

    public ?bool $downloadsScheduleEnabled = true;
    public ?bool $healthscoresScheduleEnabled = true;


    public ?Carbon $lastAlertsSchedule = null;
    public ?Carbon $lastEndpointsSchedule = null;
    public ?Carbon $lastFirewallsSchedule = null;
        
    public ?Carbon $lastDownloadsSchedule = null;

    public ?Carbon $lastHealthscoresSchedule = null;
    
    public static function group(): string
    {
        return 'sc_service';
    }
}