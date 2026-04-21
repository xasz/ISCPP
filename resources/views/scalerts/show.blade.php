<x-layouts.app>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Page Header --}}
        <div>
            <div class="flex items-center gap-2 text-sm text-neutral-500 dark:text-neutral-400 mb-1">
                <a href="{{ route('scalerts.index') }}" class="hover:underline">Alerts</a>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-neutral-700 dark:text-neutral-300">{{ $scalert->id }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $scalert->description }}</h1>
                @php
                    $color = match(strtolower($scalert->severity ?? '')) {
                        'high'   => 'red',
                        'medium' => 'yellow',
                        'low'    => 'blue',
                        default  => 'neutral',
                    };
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-400">
                    {{ $scalert->severity }}
                </span>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <x-tab-container defaultTab="details">
            <x-slot name="tabs">
                <x-tab-button name="details" label="Alert Details" />
                <x-tab-button name="raw" label="Raw JSON" />
            </x-slot>

            <x-slot name="content">

                {{-- Details Tab --}}
                <x-tab-panel name="details">
                    <x-card title="Alert Details">
                        <dl>
                            <x-card-details-row label="ID" :value="$scalert->id" />
                            <x-card-details-row label="Raised at" :value="$scalert->raisedAt" />
                            <x-card-details-row label="Severity" :value="$scalert->severity" />
                            <x-card-details-row label="Type" :value="$scalert->type" />
                            <x-card-details-row label="Category" :value="$scalert->category" />
                            <x-card-details-row label="Description" :value="$scalert->description" />
                            <x-card-details-row label="Product" :value="$scalert->product" />
                            <x-card-details-row label="Tenant" :value="$scalert->sctenant != null ? $scalert->sctenant->name : __('Unknown')" />
                            @if($webhooksEnabled ?? false)
                                <x-card-details-row label="Webhook Status" :value="$scalert->webhook_sent" />
                            @endif
                        </dl>

                        @if(collect($scalert->allowedActions)->contains('acknowledge'))
                            <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-800">
                                <livewire:scalerts.acknowledged :scalert="$scalert" />
                            </div>
                        @endif

                        @if(($webhooksEnabled ?? false) && !Str::is($scalert->webhook_sent, 'pending'))
                            <div class="mt-4">
                                <x-a-button href="{{ route('scalerts.dispatchAndShow', $scalert) }}">
                                    {{ __('Resend Webhook') }}
                                </x-a-button>
                            </div>
                        @endif
                    </x-card>
                </x-tab-panel>

                {{-- Raw JSON Tab --}}
                <x-tab-panel name="raw">
                    <x-card title="Raw JSON Data">
                        <x-card-details-json :json="$scalert->rawData" />
                    </x-card>
                </x-tab-panel>

            </x-slot>
        </x-tab-container>

    </div>
</x-layouts.app>
