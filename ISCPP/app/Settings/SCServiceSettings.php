<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SCServiceSettings extends Settings
{
    public ?string $clientSecret = '';
    public ?string $clientId = '';
    public ?string $partnerId = null;


    public ?string $token = null;
    public ?string $refresh_token = null;
    public ?string $token_expires_at = null;
        
    public static function group(): string
    {
        return 'sc_service';
    }
}