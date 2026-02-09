<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\Log;

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
        Log::log('info', "Event: $event - Message: $message");
        self::log($event, 'info', ['message' => $message]);
    }

    public static function logWarning(string $event, string $message): void
    {
        Log::log('warning', "Event: $event - Message: $message");
        self::log($event, 'warning', ['message' => $message]);
    }

    public static function logError(string $event, string $message): void
    {
        Log::log('error', "Event: $event - Message: $message");
        self::log($event, 'error', ['message' => $message]);
    }

    public static function throwError(string $event, string $message): void
    {
        Log::log('error', "Event: $event - Message: $message");
        self::log($event, 'error', ['message' => $message]);
        throw new Exception($message);
    }


    public static function logDebug(string $event, string $message): void
    {
        if(config('app.debug', false)){
            Log::log('debug', "Event: $event - Message: $message");
            self::log($event, 'debug', ['message' => $message]);
        }
    }




}
