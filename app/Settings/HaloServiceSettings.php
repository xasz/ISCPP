<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HaloServiceSettings extends Settings
{
    public ?bool $enabled = false;
    public ?string $instance = '';
    public ?string $url = '';
    
    public ?string $scope = '';
    public ?string $clientSecret = '';
    public ?string $clientId = '';

    public ?string $token = '';
    public ?string $token_expires_at = '';
        
    public static function group(): string
    {
        return 'halo_service';
    }
}