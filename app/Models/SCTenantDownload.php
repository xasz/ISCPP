<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCTenantDownload extends Model
{
    protected $table = 'sc_tenant_downloads';

    protected $fillable = [
        'rawData',
        'tenantId',
    ];
    protected $casts = [
        'rawData' => 'array',
    ];
    public function SCTenant()
    {
        return $this->belongsTo(SCTenant::class, 'tenantId');
    }
    
}
