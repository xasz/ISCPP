<x-scalerts.show.layout :scalert="$scalert">
    <x-header-card :title="$scalert->description" icon="bell-alert" icon-color="blue">
        <x-scalerts.badge :scalert="$scalert" />
        <flux:badge color="zinc" size="sm">{{ $scalert->category }}</flux:badge>
        <flux:badge color="violet" size="sm">{{ $scalert->type }}</flux:badge>
    </x-header-card>

    <x-card title="Alert Details">
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
    </x-card>

    <x-card title="Actions"> 
        @if(collect($scalert->allowedActions)->contains('acknowledge'))
            <livewire:scalerts.acknowledged :scalert="$scalert" />
        @else
            <flux:callout icon="information-circle">
                Alert cannot be acknowledged. No acknowledge action available.
            </flux:callout>
        @endif

        @if(($webhooksEnabled ?? false) && !Str::is($scalert->webhook_sent, 'pending'))
            <x-a-button href="{{ route('scalerts.dispatchAndShow', $scalert) }}">
                {{ __('Resend Webhook') }}
            </x-a-button>
        @else
            <flux:callout icon="information-circle">
                Webhook cannot be resent. Webhooks are either disabled or the alert is still pending dispatch.
            </flux:callout>
        @endif
    </x-card>

</x-scalerts.show.layout>