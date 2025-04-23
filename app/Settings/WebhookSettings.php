<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebhookSettings extends Settings
{
    public ?bool $enabled = false;
    public ?string $url = '';
    public ?string $payload = ''; # this is planned for custimzing the payload
    public ?string $header = ''; # this is planned for adding custom headers

    public ?bool $basicAuthEnabled = false;
    public ?string $basicAuthUsername = '';
    public ?string $basicAuthPassword = '';

    public ?bool $enableHaloData = false;


    public static function group(): string
    {
        return 'webhook';
    }
}