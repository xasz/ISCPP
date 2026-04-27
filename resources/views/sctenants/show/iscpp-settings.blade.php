<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="ISCPP Settings"> 
        <!-- Halo Settings Tab -->
        @if(app(App\Settings\HaloServiceSettings::class)->enabled)
            <livewire:sctenant.card-halosettings :sctenant="$sctenant" />
        @else
            <flux:callout icon="information-circle">
                Halo Service is disabled. Please enable it to configure settings.
            </flux:callout>
        @endif
        <!-- Ninja Settings Tab -->
        @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
            <livewire:sctenant.card-ninjasettings :sctenant="$sctenant" />
        @else
            <flux:callout icon="information-circle">
                Ninja Service is disabled. Please enable it to configure settings.
            </flux:callout>
        @endif
    </x-card>
</x-sctenants.show.layout>