<x-layouts.app>
    <x-card>
        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
            <div>
                <x-card-details-row label="ID" :value="$scalert->id" />
                <x-card-details-row label="raisedAt" :value="$scalert->raisedAt" />
                <x-card-details-row label="severity" :value="$scalert->severity" />
                <x-card-details-row label="type" :value="$scalert->type" />
                <x-card-details-row label="category" :value="$scalert->category" />
                <x-card-details-row label="description" :value="$scalert->description" />
                <x-card-details-row label="product" :value="$scalert->product" />
                <x-card-details-row label="tenant" :value="$scalert->sctenant != null ? $scalert->sctenant->name : __('Unkown')" />
                @if($webhooksEnabled ?? false)
                <x-card-details-row label="webhook_sent" :value="$scalert->webhook_sent" />
                    @if(!Str::is($scalert->webhook_sent, 'pending'))
                        <x-a-button href="{{ route('scalerts.dispatchAndShow', $scalert) }}">
                            {{ __('Resend Webhook') }}
                        </x-a-button>
                    @endif
                @endif


            </div>
            <div class="relative overflow-hidden">
                <x-card-details-json :json="$scalert->rawData" />
            </div>
        </div>
    </x-card>
</x-layouts.app>