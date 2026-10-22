<?php

namespace App\Events;

enum ISCPPEventSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
    case Critical = 'critical';

    public function badgeColor(): string
    {
        return match ($this) {
            self::Info => 'blue',
            self::Warning => 'amber',
            self::Error => 'red',
            self::Critical => 'red',
        };
    }
}
