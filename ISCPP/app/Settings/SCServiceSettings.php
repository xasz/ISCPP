<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SCServiceSettings extends Settings
{
    public ?string $clientSecret = '';
    public ?string $clientId = '';
    public ?string $partnerId = '';


    public ?string $token = '';
    public ?string $refresh_token = '';
    public ?string $token_expires_at = '';
        
    public static function group(): string
    {
        return 'sc_service';
    }
}