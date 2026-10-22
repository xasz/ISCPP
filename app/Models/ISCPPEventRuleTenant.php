<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenant selection for a rule.
 *
 * Intentionally not cascaded from sc_tenants: a tenant lost to a transient Sophos API
 * fault must not silently erase the rule watching it, so rows survive and rebind when
 * the tenant returns.
 */
class ISCPPEventRuleTenant extends Model
{
    public $timestamps = false;

    protected $table = 'iscpp_event_rule_tenants';

    protected $fillable = [
        'iscpp_event_rule_id',
        'tenantId',
    ];

    public function rule()
    {
        return $this->belongsTo(ISCPPEventRule::class, 'iscpp_event_rule_id');
    }

    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }
}
