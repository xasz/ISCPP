<x-layouts.app>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400 mb-1">
                    <a href="{{ route('sctenants.index') }}" class="hover:underline">Tenants</a>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span class="text-neutral-700 dark:text-neutral-300">{{ $sctenant->name }}</span>
                </div>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $sctenant->name }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $sctenant->showAs }}</p>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <x-tab-container defaultTab="details">
            <x-slot name="tabs">
                <x-tab-button name="details" label="Details" />
                <x-tab-button name="alerts" label="Alerts" />
                <x-tab-button name="billables" label="Billables" />
                <x-tab-button name="downloads" label="Downloads" />
                <x-tab-button name="healthscore" label="Healthscore" />
                @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                    <x-tab-button name="halo" label="Halo" />
                @endif
                @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
                    <x-tab-button name="ninja" label="NinjaOne" />
                @endif
                <x-tab-button name="raw" label="Raw JSON" />
            </x-slot>

            <x-slot name="content">

                {{-- Details Tab --}}
                <x-tab-panel name="details" class="space-y-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-card title="Basic Information">
                            <dl>
                                <x-card-details-row label="Name" :value="$sctenant->name" />
                                <x-card-details-row label="Display Name" :value="$sctenant->showAs" />
                                <x-card-details-row label="Data Geography" :value="$sctenant->dataGeography" />
                                <x-card-details-row label="Data Region" :value="$sctenant->dataRegion" />
                                <x-card-details-row label="Billing Type" :value="$sctenant->billingType" />
                            </dl>
                        </x-card>

                        <x-card title="Technical Details">
                            <dl>
                                <x-card-details-row label="ID" :value="$sctenant->id" />
                                <x-card-details-row label="Partner ID" :value="$sctenant->partnerId" />
                                <x-card-details-row label="Organization ID" :value="$sctenant->organizationId" />
                                <x-card-details-row label="API Host" :value="$sctenant->apiHost" />
                            </dl>
                        </x-card>
                    </div>
                </x-tab-panel>

                {{-- Alerts Tab --}}
                <x-tab-panel name="alerts">
                    <x-card title="Alerts">
                        <x-scalerts-table :scalerts="$scalerts" :hideSCTenant="true" />
                    </x-card>
                </x-tab-panel>

                {{-- Billables Tab --}}
                <x-tab-panel name="billables">
                    <x-card title="Billable Items">
                        <x-scbillables-table :scbillables="$scbillables" :hideSCTenant="true" />
                    </x-card>
                </x-tab-panel>

                {{-- Downloads Tab --}}
                <x-tab-panel name="downloads">
                    <x-card title="Downloads">
                        @if($sctenant->SCTenantDownload()->count() > 0)
                            <dl>
                                <x-card-details-row label="Windows" :value="$sctenant->SCTenantDownload->getWindowsInstallerUrl()" />
                                <x-card-details-row label="Linux" :value="$sctenant->SCTenantDownload->getLinuxInstallerUrl()" />
                                <x-card-details-row label="macOS" :value="$sctenant->SCTenantDownload->getMacOSInstallerUrl()" />
                            </dl>
                            <x-card-details-json :arr="$sctenant->SCTenantDownload->rawData" />
                        @else
                            <p class="text-sm text-neutral-400 dark:text-neutral-500 py-2">{{ __('No downloads available for this tenant.') }}</p>
                        @endif
                    </x-card>
                </x-tab-panel>

                {{-- Healthscore Tab --}}
                <x-tab-panel name="healthscore">
                    <x-card title="Healthscore">
                        @if($sctenant->SCTenantHealthscore()->count() > 0)
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 mb-6">
                                @if($sctenant->SCTenantHealthscore->hasEndpointProtectionHealthscore())
                                    <x-card-simple-info title="EP Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointProtectionComputerHealthscore() }}" />
                                    <x-card-simple-info title="EP Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointProtectionServerHealthscore() }}" />
                                @endif

                                @if($sctenant->SCTenantHealthscore->hasEndpointPolicyHealthscore())
                                    <x-card-simple-info title="Policy Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointPolicyComputerHealthscore() }}" />
                                    <x-card-simple-info title="Policy Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointPolicyServerHealthscore() }}" />
                                @endif

                                @if($sctenant->SCTenantHealthscore->hasEndpointExclusionsHealthscore())
                                    <x-card-simple-info title="Exclusions Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsComputerHealthscore() }}" />
                                    <x-card-simple-info title="Exclusions Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsServerHealthscore() }}" />
                                    <x-card-simple-info title="Exclusions Global" value="{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsGlobalHealthscore() }}" />
                                @endif

                                @if($sctenant->SCTenantHealthscore->hasEndpointTamperProtectionHealthscore())
                                    <x-card-simple-info title="Tamper Computer" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionComputerHealthscore() }}" />
                                    <x-card-simple-info title="Tamper Server" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionServerHealthscore() }}" />
                                    <x-card-simple-info title="Tamper Global" value="{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionGlobalHealthscore() }}" />
                                @endif

                                @if($sctenant->SCTenantHealthscore->hasEndpointMDRDataTelemetryHealthscore())
                                    <x-card-simple-info title="MDR Data Telemetry" value="{{ $sctenant->SCTenantHealthscore->getEndpointMDRDataTelemetryProtectionImprovementHealthscore() }}" />
                                @endif

                                @if($sctenant->SCTenantHealthscore->hasEndpointMDRContactHealthscore())
                                    <x-card-simple-info title="MDR Contact" value="{{ $sctenant->SCTenantHealthscore->getEndpointMDRContactHealthscore() }}" />
                                @endif
                            </div>
                            <x-card-details-json :arr="$sctenant->SCTenantHealthscore->rawData" />
                        @else
                            <p class="text-sm text-neutral-400 dark:text-neutral-500 py-2">{{ __('No Healthscore available for this tenant.') }}</p>
                        @endif
                    </x-card>
                </x-tab-panel>

                {{-- Halo Settings Tab --}}
                @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                <x-tab-panel name="halo">
                    <livewire:sctenant.card-halosettings :sctenant="$sctenant" />
                </x-tab-panel>
                @endif

                {{-- NinjaOne Settings Tab --}}
                @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
                <x-tab-panel name="ninja">
                    <livewire:sctenant.card-ninjasettings :sctenant="$sctenant" />
                </x-tab-panel>
                @endif

                {{-- Raw JSON Tab --}}
                <x-tab-panel name="raw">
                    <x-card title="Raw JSON Data">
                        <x-card-details-json :json="$sctenant->rawData" />
                    </x-card>
                </x-tab-panel>

            </x-slot>
        </x-tab-container>

        <livewire:sctenant.card-commands-sctenants :sctenant="$sctenant" />

    </div>
</x-layouts.app>
