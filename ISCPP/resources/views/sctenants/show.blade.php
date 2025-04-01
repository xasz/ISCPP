<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <x-card>
                <flux:heading size="lg" level="1" class="mb-6">Tenant Details</flux:heading>    
                <x-card-details-row label="Name" :value="$sctenant->name" />
                <x-card-details-row label="Display Name" :value="$sctenant->showAs" />
                <x-card-details-row label="Data Geography" :value="$sctenant->dataGeography" />
                <x-card-details-row label="Data Region" :value="$sctenant->dataRegion" />
                <x-card-details-row label="Billing Type" :value="$sctenant->billingType" />
                <flux:separator variant="subtle"  class="my-8"/>
                <x-card-details-row label="ID" :value="$sctenant->id" />
                <x-card-details-row label="Partner Id" :value="$sctenant->partnerId" />
                <x-card-details-row label="Organization Id" :value="$sctenant->organizationId" />
                <x-card-details-row label="API Host" :value="$sctenant->apiHost" />
            </x-card>
            <x-card>
                <x-card-details-json :json="$sctenant->rawData" />
            </x-card>
        </div>
        @if(app(App\Settings\HaloServiceSettings::class)->enabled)
            <livewire:sctenant.card-halosettings :sctenant="$sctenant" />            
        @endif
        <x-card>
            <x-scalerts-table :scalerts="$scalerts" hideSCTenant=true/>
        </x-card>
        <x-card>
            <x-scbillables-table :scbillables="$scbillables" hideSCTenant=true />
            @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                <x-a-button href="{{ route('scbillables.dispatchToHaloAndShowTenant', [
                    'year' => 2025,
                    'month' => 3,
                    'id' => $sctenant
                ]) }}">
                    {{ __('Send To Halo') }}
                </x-a-button>
            @endif 
        </x-card>
    </div>
</x-layouts.app>