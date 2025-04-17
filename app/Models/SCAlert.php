<?php

namespace App\Models;

use App\Jobs\SendSCAlertWebhook;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SCAlert extends Model
{
    use HasFactory;

    protected $table = 'sc_alerts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'allowedActions',
        'category',
        'description',
        'groupKey',
        'managedAgentID',
        'managedAgentName',
        'managedAgentType',
        'personID',
        'personName',
        'product',
        'raisedAt',
        'severity',
        'tenantId',
        'type',
        'rawData',
        'webhook_sent',
    ];

    protected $casts = [
        'allowedActions' => 'array',
        'raisedAt' => 'datetime'
    ];

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }

    public function webhookLog()
    {
        return $this->hasMany(WebhookLog::class, 'sc_alert_id');
    }

    public function dispatchWebhook()
    {
        $this->update(['webhook_sent' => 'pending']);
        SendSCAlertWebhook::dispatch($this);
    }

    public function getColorTailwindColor(){
        return match ($this->severity) {
            'high' => 'red-600',
            'medium' => 'yellow-600',
            default => 'grey-600',
        };
    }
}
