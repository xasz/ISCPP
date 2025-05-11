<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SCTenantHealthscore extends Model
{
    protected $table = 'sc_tenant_healthscores';

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
    
    public function hasEndpointProtectionHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['protection']) && count($this->rawData['endpoint']['protection']) > 0;
    }
    public function hasEndpointPolicyHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['policy']) && count($this->rawData['endpoint']['policy']) > 0;
    }
    
    public function hasEndpointExclusionsHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['exclusions']) && count($this->rawData['endpoint']['exclusions']) > 0;
    }
    
    public function hasEndpointTamperProtectionHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['tamperProtection']) && count($this->rawData['endpoint']['tamperProtection']) > 0;
    }
    
    public function hasEndpointMDRDataTelemetryHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['mdrDataTelemetry']) && count($this->rawData['endpoint']['mdrDataTelemetry']) > 0;
    }
    
    public function hasEndpointMDRContactHealthscore(){
        return $this->rawData && isset($this->rawData['endpoint']['mdrAuthorizedContact']) && count($this->rawData['endpoint']['mdrDataTelemetry']) > 0;
    }


    public function getEndpointProtectionComputerHealthscore(){
        if(!$this->hasEndpointProtectionHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['protection']['computer']['score'] ?? -1; 
    }

    public function getEndpointProtectionServerHealthscore(){
        if(!$this->hasEndpointProtectionHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['protection']['server']['score'] ?? -1; 
    }

    public function getEndpointPolicyComputerHealthscore(){
        if(!$this->hasEndpointPolicyHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['policy']['computer']['threat-protection']['score'] ?? -1; 
    }

    public function getEndpointPolicyServerHealthscore(){
        if(!$this->hasEndpointPolicyHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['policy']['server']['server-threat-protection']['score'] ?? -1; 
    }

    public function getEndpointExclusionsComputerHealthscore(){
        if(!$this->hasEndpointExclusionsHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['exclusions']['policy']['computer']['score'] ?? -1; 
    }

    public function getEndpointExclusionsServerHealthscore(){
        if(!$this->hasEndpointExclusionsHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['exclusions']['policy']['server']['score'] ?? -1; 
    }

    public function getEndpointExclusionsGlobalHealthscore(){
        if(!$this->hasEndpointExclusionsHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['exclusions']['global']['score'] ?? -1; 
    }

    public function getEndpointTamperProtectionComputerHealthscore(){
        if(!$this->hasEndpointTamperProtectionHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['tamperProtection']['computer']['score'] ?? -1; 
    }

    public function getEndpointTamperProtectionServerHealthscore(){
        if(!$this->hasEndpointTamperProtectionHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['tamperProtection']['server']['score'] ?? -1; 
    }

    public function getEndpointTamperProtectionGlobalHealthscore(){
        if(!$this->hasEndpointTamperProtectionHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['tamperProtection']['globalDetail']['score'] ?? -1; 
    }

    public function getEndpointMDRDataTelemetryProtectionImprovementHealthscore(){
        if(!$this->hasEndpointMDRDataTelemetryHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['mdrDataTelemetry']['protectionImprovement']['score'] ?? -1; 
    }

    public function getEndpointMDRContactHealthscore(){
        if(!$this->hasEndpointMDRContactHealthscore()) {
            return -1;
        }
        return $this->rawData['endpoint']['mdrAuthorizedContact']['contact']['score'] ?? -1; 
    }


}
