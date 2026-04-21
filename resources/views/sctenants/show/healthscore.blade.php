<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Healthscore"> 
        @if($sctenant->SCTenantHealthscore()->count() > 0)
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                @if($sctenant->SCTenantHealthscore->hasEndpointProtectionHealthscore())
                    <x-card-simple-info title="Endpoint Protection Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointProtectionComputerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Protection Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointProtectionServerHealthscore() }}" />
                @endif
                
                @if($sctenant->SCTenantHealthscore->hasEndpointPolicyHealthscore())
                    <x-card-simple-info title="Endpoint Policy Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointPolicyComputerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Policy Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointPolicyServerHealthscore() }}" />
                @endif

                @if($sctenant->SCTenantHealthscore->hasEndpointExclusionsHealthscore())
                    <x-card-simple-info title="Endpoint Policy Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsComputerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Policy Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsServerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Policy Global" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsGlobalHealthscore() }}" />
                @endif         

                @if($sctenant->SCTenantHealthscore->hasEndpointTamperProtectionHealthscore())
                    <x-card-simple-info title="Endpoint Tamper Protection Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionComputerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Tamper Protection Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionServerHealthscore() }}" />
                    <x-card-simple-info title="Endpoint Tamper Protection Global" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionGlobalHealthscore() }}" />
                @endif                               

                @if($sctenant->SCTenantHealthscore->hasEndpointMDRDataTelemetryHealthscore())
                    <x-card-simple-info title="Endpoint MDR Data Telemetry" value="{{ $sctenant->SCTenantHealthscore->getEndpointMDRDataTelemetryProtectionImprovementHealthscore() }}" />
                @endif       

                @if($sctenant->SCTenantHealthscore->hasEndpointMDRContactHealthscore())
                    <x-card-simple-info title="Endpoint MDR Contact" value="{{ $sctenant->SCTenantHealthscore->getEndpointMDRContactHealthscore() }}" />
                @endif       
            </div>
            <x-card-details-json :arr="$sctenant->SCTenantHealthscore->rawData" />
        @else
            <flux:text>
                {{ __('No Healthscore available for this tenant.') }}
            </flux:text>
        @endif
    </x-card>
</x-sctenants.show.layout>