<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebhookSettings extends Settings
{
    public ?bool $enabled = false;
    public ?string $url = '';
    public ?string $payload = ''; # this is planned for custimzing the payload
    public ?string $header = ''; # this is planned for adding custom headers
        
    public static function group(): string
    {
        return 'webhook';
    }
}