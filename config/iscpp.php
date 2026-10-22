<?php

return [

    'user_timezone' => env('USER_DEFAULT_TIMEZONE', 'Europe/Berlin'),

    /*
    |--------------------------------------------------------------------------
    | ISCPP Event System
    |--------------------------------------------------------------------------
    |
    | The new ISCPP event system. This is independent of the legacy system log
    | in App\Models\Event and of the legacy SCAlert webhook.
    |
    */

    'events' => [
        // Master switch. Disabled during tests so existing suites are unaffected.
        'enabled' => env('ISCPP_EVENTS_ENABLED', true),

        // Days to keep iscpp_events. Deliveries cascade with their event.
        'retention_days' => env('ISCPP_EVENTS_RETENTION_DAYS', 90),
    ],

    'webhook' => [
        'timeout' => env('ISCPP_WEBHOOK_TIMEOUT', 10),

        // Consecutive terminal failures before a webhook disables itself.
        'failure_threshold' => env('ISCPP_WEBHOOK_FAILURE_THRESHOLD', 10),

        // Allow webhooks to point at private/loopback addresses. Off by default (SSRF).
        'allow_private_hosts' => env('ISCPP_WEBHOOK_ALLOW_PRIVATE_HOSTS', false),
    ],

    'healthscore' => [
        // Used when a tenant has no explicit threshold for a score.
        'default_threshold' => env('ISCPP_HEALTHSCORE_DEFAULT_THRESHOLD', 100),
    ],

];
