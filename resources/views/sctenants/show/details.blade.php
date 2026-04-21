<x-sctenants.show.layout :sctenant="$sctenant">

    <x-card title="Tenant">
        <div class="flex items-end gap-4">
            <div>
                <flux:icon name="building-office-2" class="h-16 w-16 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="flex flex-col gap-2">
                <flux:heading size="xl" level="1" class="truncate">{{ $sctenant->name }}</flux:heading>
                <div>
                    <flux:badge size="sm">{{ ucfirst($sctenant->billingType) }}</flux:badge>
                    <flux:badge color="zinc" size="sm">{{ $sctenant->dataGeography }}</flux:badge>
                    <flux:badge color="violet" size="sm">{{ $sctenant->dataRegion }}</flux:badge>
                </div>
            </div>
        </div>
    </x-card>


    <div class="flex flex-col gap-4">
        <x-card title="Basic Information">
            <x-card-details-row icon="building-office-2" label="Name" :value="$sctenant->name" />
            <x-card-details-row icon="tag" label="Display Name" :value="$sctenant->showAs" />
            <x-card-details-row icon="map-pin" label="Data Geography" :value="$sctenant->dataGeography" />
            <x-card-details-row icon="globe-alt" label="Data Region" :value="$sctenant->dataRegion" />
            <x-card-details-row icon="credit-card" label="Billing Type" :value="$sctenant->billingType" />
        </x-card>
        
        <!-- Technical Details Card -->
        <x-card title="Technical Details">
            <x-card-details-row icon="identification" label="ID" :value="$sctenant->id" />
            <x-card-details-row icon="link" label="Partner Id" :value="$sctenant->partnerId" />
            <x-card-details-row icon="organization" label="Organization Id" :value="$sctenant->organizationId" />
            <x-card-details-row icon="server" label="API Host" :value="$sctenant->apiHost" />
        </x-card>

        <x-card title="Downloads"> 
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
    </div>
</x-sctenants.show.layout>