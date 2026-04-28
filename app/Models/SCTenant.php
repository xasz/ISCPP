<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'ninjaorg_id',
        'iscpp_ignore',
    ];

    protected function casts(): array
    {
        return [
            'haloclient_id' => 'integer',
            'iscpp_ignore' => 'boolean',
        ];
    }

    public function scopeNotIgnored(Builder $query): Builder
    {
        return $query->where('iscpp_ignore', false);
    }

    public function SCAlerts()
    {
        return $this->hasMany(SCAlert::class, 'tenantId');
    }

    public function SCBillables()
    {
        return $this->hasMany(SCBillable::class, 'tenantId');
    }

    public function SCTenantDownload()
    {
        return $this->hasOne(SCTenantDownload::class, 'tenantId');
    }

    public function SCTenantHealthscore()
    {
        return $this->hasOne(SCTenantHealthscore::class, 'tenantId');
    }

    public function SCEndpoints()
    {
        return $this->hasMany(SCEndpoint::class, 'tenantId');
    }

    public function SCFirewalls()
    {
        return $this->hasMany(SCFirewall::class, 'tenantId');
    }
}
