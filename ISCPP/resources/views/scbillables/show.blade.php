<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <x-card>
                <flux:heading size="lg" level="1" class="mb-6">Billable Details</flux:heading>    
                <x-card-details-row label="Month" :value="$scbillable->month" />
                <x-card-details-row label="Year" :value="$scbillable->year" />
                <x-card-details-row label="Tenant ID" :value="$scbillable->tenantId" />
                <x-card-details-row label="Tenant Name" :value="$scbillable->SCTenant->name" />
                <x-card-details-row label="orderLineItemNumber" :value="$scbillable->orderLineItemNumber" />
                <x-card-details-row label="productGroup" :value="$scbillable->productGroup" />
                <x-card-details-row label="billableQuantity" :value="$scbillable->billableQuantity" />
                <x-card-details-row label="orderedQuantity" :value="$scbillable->orderedQuantity" />
                <x-card-details-row label="actualQuantity" :value="$scbillable->actualQuantity" />
                <x-card-details-row label="productCode" :value="$scbillable->productCode" />
                <x-card-details-row label="sku" :value="$scbillable->sku" />
                <x-card-details-row label="productDescription" :value="$scbillable->productDescription" /> 
                @if(app(App\Settings\HaloServiceSettings::class)->enabled)
                <x-card-details-row label="Halo Status" :value="$scbillable->sent_to_halo" />
                @endif                   
            </x-card>
            <x-card>
                <x-card-details-json :json="$scbillable->rawData" />
            </x-card>
        </div>
    </div>
</x-layouts.app>