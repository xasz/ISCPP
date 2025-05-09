<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SCTenant extends Model
{
    use HasFactory;

    protected $table = 'sc_tenants';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'showAs',
        'name',
        'dataGeography',
        'dataRegion',
        'billingType',
        'partnerId',
        'organizationId',
        'apiHost',
        'rawData',
        'haloclient_id',
    ];

    protected $casts = [
        'haloclient_id' => 'integer',
    ];

    function SCAlerts()
    {
        return $this->hasMany(SCAlert::class, 'tenantId');
    }

    function SCBillables()
    {
        return $this->hasMany(SCBillable::class, 'tenantId');
    }

    function SCTenantDownload()
    {
        return $this->hasOne(SCTenantDownload::class, 'tenantId');
    }

    function SCTenantHealthscore()
    {
        return $this->hasOne(SCTenantHealthscore::class, 'tenantId');
    }
}
