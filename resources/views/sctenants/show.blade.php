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
                            <x-card-details-json :json="$sctenant->rawData" />
                        @else
                            <flux:text>
                                {{ __('No downloads available for this tenant.') }}
                            </flux:text>
                        @endif
                    </x-card>
                </x-tab-panel>


                <!-- Downloads Tab -->
                <x-tab-panel name="healthscore">
                    <x-card class="overflow-hidden" title="Healthscore"> 
                        @if($sctenant->SCTenantHealthscore()->count() > 0)
                            <x-card-details-json :json="$sctenant->rawData" />
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
                
                <!-- Raw Data Tab -->
                <x-tab-panel name="raw">
                    <x-card class="overflow-hidden" title="Raw JSON Data">
                        <x-card-details-json :json="$sctenant->rawData" />
                    </x-card>
                </x-tab-panel>
            </x-slot>
        </x-tab-container>
        </div>
    </div>
</x-layouts.app>