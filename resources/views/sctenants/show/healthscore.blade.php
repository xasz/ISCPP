<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Healthscore"> 
        @if($sctenant->SCTenantHealthscore()->count() > 0)
            <div class="grid grid-cols-1 gap-4">
                @if($sctenant->SCTenantHealthscore->hasEndpointProtectionHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint Protection</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointProtectionComputerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Protection Computer</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointProtectionServerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Protection Server</flux:text>
                    </div>
                @endif
                
                @if($sctenant->SCTenantHealthscore->hasEndpointPolicyHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint Policy</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointPolicyComputerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Policy Computer</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointPolicyServerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Policy Server</flux:text>
                    </div>
                @endif

                @if($sctenant->SCTenantHealthscore->hasEndpointExclusionsHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint Exclusions</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointExclusionsComputerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Exclusions Computer</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointExclusionsServerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Exclusions Server</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointExclusionsGlobalHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Exclusions Global</flux:text>
                    </div>
                @endif

                @if($sctenant->SCTenantHealthscore->hasEndpointTamperProtectionHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint Tamper Protection</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointTamperProtectionComputerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Tamper Protection Computer</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointTamperProtectionServerHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Tamper Protection Server</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointTamperProtectionGlobalHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint Tamper Protection Global</flux:text>
                    </div>
                @endif           

                @if($sctenant->SCTenantHealthscore->hasEndpointMDRDataTelemetryHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint MDR Data Telemetry</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointMDRDataTelemetryProtectionImprovementHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint MDR Data Telemetry Protection Improvement</flux:text>
                    </div>
                @endif       

                @if($sctenant->SCTenantHealthscore->hasEndpointMDRContactHealthscore())
                    <flux:heading size="lg" level="2" class="col-span-full">Endpoint MDR Contact</flux:heading>
                    <div class="flex items-center gap-2">
                        <x-healthscore-badge :score="$sctenant->SCTenantHealthscore->getEndpointMDRContactHealthscore()" size="lg" />
                        <flux:text size="lg">Endpoint MDR Contact</flux:text>
                    </div>
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