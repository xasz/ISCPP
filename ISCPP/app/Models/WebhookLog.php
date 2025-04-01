<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $table = 'webhook_log';
    public $incrementing = false;

    protected $fillable = [
        'sc_alert_id',
        'payload',
        'url',
        'response',
        'statusCode',
    ];
    
    protected $casts = [
        'payload' => 'array',
    ];

    public function SCAlert()
    {
        return $this->belongsTo(SCAlert::class, 'sc_alert_id');
    }
}