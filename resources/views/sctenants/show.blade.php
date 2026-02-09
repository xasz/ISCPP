<x-layouts.app>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header with Tenant Name and Actions -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $sctenant->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sctenant->showAs }}</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <x-tab-container defaultTab="details">
            <x-slot name="tabs">
                <x-tab-button name="details" label="Tenant Details" />
                <x-tab-button name="alerts" label="Alerts" />
                <x-tab-button name="billables" label="Billables" />
                <x-tab-button name="downloads" label="Downloads" />
                <x-tab-button name="healthscore" label="Healthscore" />
                @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                    <x-tab-button name="halo" label="Halo Settings" />
                @endif
                @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
                    <x-tab-button name="ninja" label="NinjaOne Settings" />
                @endif
                <x-tab-button name="raw" label="Raw" />
            </x-slot>
            
            <x-slot name="content">
                <!-- Tenant Details Tab -->
                <x-tab-panel name="details" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Basic Info Card -->
                        <x-card class="overflow-hidden" title="Basic Information">
                            <div class="space-y-4">
                                <x-card-details-row label="Name" :value="$sctenant->name" />
                                <x-card-details-row label="Display Name" :value="$sctenant->showAs" />
                                <x-card-details-row label="Data Geography" :value="$sctenant->dataGeography" />
                                <x-card-details-row label="Data Region" :value="$sctenant->dataRegion" />
                                <x-card-details-row label="Billing Type" :value="$sctenant->billingType" />
                            </div>
                        </x-card>
                        
                        <!-- Technical Details Card -->
                        <x-card class="overflow-hidden" title="Technical Details">
                            <div class="space-y-4">
                                <x-card-details-row label="ID" :value="$sctenant->id" />
                                <x-card-details-row label="Partner Id" :value="$sctenant->partnerId" />
                                <x-card-details-row label="Organization Id" :value="$sctenant->organizationId" />
                                <x-card-details-row label="API Host" :value="$sctenant->apiHost" />
                            </div>
                        </x-card>
                    </div>
                </x-tab-panel>

                <!-- Alerts Tab -->
                <x-tab-panel name="alerts">
                    <x-card class="overflow-hidden" title="Tenant Alerts">
                        <x-scalerts-table :scalerts="$scalerts" hideSCTenant=true/>
                    </x-card>
                </x-tab-panel>

                <!-- Billables Tab -->
                <x-tab-panel name="billables">
                    <x-card class="overflow-hidden" title="Billable Items"> 
                        <x-scbillables-table :scbillables="$scbillables" hideSCTenant=true />
                    </x-card>
                </x-tab-panel>

                
                <!-- Downloads Tab -->
                <x-tab-panel name="downloads">
                    <x-card class="overflow-hidden" title="Downloads"> 
                        @if($sctenant->SCTenantDownload()->count() > 0)
                        <x-card-details-row label="Windows Installer Url" :value="$sctenant->SCTenantDownload->getWindowsInstallerUrl()" />
                        <x-card-details-row label="Linux Installer Url" :value="$sctenant->SCTenantDownload->getLinuxInstallerUrl()" />
                        <x-card-details-row label="macOS Installer Url" :value="$sctenant->SCTenantDownload->getMacOSInstallerUrl()" />
                        <x-card-details-json :arr="$sctenant->SCTenantDownload->rawData" />
                        @else
                            <flux:text>
                                {{ __('No downloads available for this tenant.') }}
                            </flux:text>
                        @endif
                    </x-card>
                </x-tab-panel>


                <!-- Healtscore Tab -->
                <x-tab-panel name="healthscore">
                    <x-card class="overflow-hidden" title="Healthscore"> 
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
                </x-tab-panel>

                <!-- Halo Settings Tab -->
                @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                <x-tab-panel name="halo">
                    <livewire:sctenant.card-halosettings :sctenant="$sctenant" />
                </x-tab-panel>
                @endif
                <!-- Halo Settings Tab -->
                @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
                <x-tab-panel name="ninja">
                    <livewire:sctenant.card-ninjasettings :sctenant="$sctenant" />
                </x-tab-panel>
                @endif
                
                
                <!-- Raw Data Tab -->
                <x-tab-panel name="raw">
                    <x-card class="overflow-hidden" title="Raw JSON Data">
                        <x-card-details-json :json="$sctenant->rawData" />
                    </x-card>
                </x-tab-panel>
            </x-slot>
        </x-tab-container>

        <livewire:sctenant.card-commands-sctenants :sctenant="$sctenant" />
        </div>
    </div>
</x-layouts.app>