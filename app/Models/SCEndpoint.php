<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCEndpoint extends Model
{
    protected $table = 'sc_endpoints';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'hostname',
        'tamperProtectionEnabled',
        'lastSeen',
        'tenantId',
        'type',
        'rawData',
        'healthStatus',
    ];
    
    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }
}
