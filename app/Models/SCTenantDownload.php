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
    
    public function getWindowsInstallerUrl(){
        return collect($this->rawData['installers'])->where('platform', 'windows')->where('type', 'computer')->pluck('downloadUrl')->first();
    }

    public function getLinuxInstallerUrl(){
        return collect($this->rawData['installers'])->where('platform', 'linux')->where('type', 'server')->pluck('downloadUrl')->first();
    }
    
    public function getMacOSInstallerUrl(){
        return collect($this->rawData['installers'])->where('platform', 'macOS')->where('type', 'computer')->pluck('downloadUrl')->first();
    }
}
