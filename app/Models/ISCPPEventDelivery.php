<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One webhook delivery attempt for one event and rule.
 *
 * The request payload is not stored here: the frozen envelope already lives on the
 * event, and copying it per delivery would duplicate it many times over.
 */
class ISCPPEventDelivery extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $table = 'iscpp_event_deliveries';

    protected $fillable = [
        'iscpp_event_id',
        'iscpp_event_rule_id',
        'attempt',
        'status',
        'http_status',
        'url',
        'response_body',
        'error',
    ];

    public function event()
    {
        return $this->belongsTo(ISCPPEventLog::class, 'iscpp_event_id');
    }

    public function rule()
    {
        return $this->belongsTo(ISCPPEventRule::class, 'iscpp_event_rule_id');
    }

    public function badgeColor(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILED => 'red',
            default => 'zinc',
        };
    }
}
