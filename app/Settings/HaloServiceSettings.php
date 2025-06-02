<?php

namespace App\Settings;

use Carbon\Carbon;
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
    public ?int $token_expires_at = null;
        
    public static function group(): string
    {
        return 'halo_service';
    }
}