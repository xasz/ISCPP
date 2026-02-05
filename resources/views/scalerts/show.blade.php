<x-layouts.app>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Alert {{ $scalert->id }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $scalert->description }}</p>
        </div>
    </div>
    <!-- Tab Navigation -->
    <x-tab-container defaultTab="details">
        <x-slot name="tabs">
            <x-tab-button name="details" label="Alert Details" />
            <x-tab-button name="raw" label="Raw" />
        </x-slot>
        
        <x-slot name="content">
            <!-- Details Tab -->
            <x-tab-panel name="details">
                <x-card class="overflow-hidden" title="Alert Details">
                    <x-card-details-row label="ID" :value="$scalert->id" />
                    <x-card-details-row label="raisedAt" :value="$scalert->raisedAt" />
                    <x-card-details-row label="severity" :value="$scalert->severity" />
                    <x-card-details-row label="type" :value="$scalert->type" />
                    <x-card-details-row label="category" :value="$scalert->category" />
                    <x-card-details-row label="description" :value="$scalert->description" />
                    <x-card-details-row label="product" :value="$scalert->product" />
                    <x-card-details-row label="tenant" :value="$scalert->sctenant != null ? $scalert->sctenant->name : __('Unkown')" />
                    @if(collect($scalert->allowedActions)->contains('acknowledge'))
                        <livewire:scalerts.acknowledged :scalert="$scalert" />
                    @endif
                    @if($webhooksEnabled ?? false)
                    <x-card-details-row label="webhook_sent" :value="$scalert->webhook_sent" />
                        @if(!Str::is($scalert->webhook_sent, 'pending'))
                            <x-a-button href="{{ route('scalerts.dispatchAndShow', $scalert) }}">
                                {{ __('Resend Webhook') }}
                            </x-a-button>
                        @endif
                    @endif
                </x-card>
            </x-tab-panel>

            <!-- Raw Data Tab -->
            <x-tab-panel name="raw">
                <x-card class="overflow-hidden" title="Raw JSON Data">
                    <x-card-details-json :json="$scalert->rawData" />
                </x-card>
            </x-tab-panel>
        </x-slot>
    </x-tab-container>
</x-layouts.app>