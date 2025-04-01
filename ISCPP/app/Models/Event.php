<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'event', 
        'type', 
        'data'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];

    public static function log(string $event, string $type, array $data): void
    {
        self::create([
            'event' => $event,
            'type' => $type,
            'data' => $data
        ]);
    }

    public static function logInfo(string $event, string $message): void
    {
        self::log($event, 'info', ['message' => $message]);
    }

    public static function logError(string $event, string $message): void
    {
        self::log($event, 'error', ['message' => $message]);
    }

    public static function throwError(string $event, string $message): void
    {
        self::log($event, 'error', ['message' => $message]);
        throw new \Exception($message);
    }

}
