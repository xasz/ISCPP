<x-sctenants.show.layout :sctenant="$sctenant">
    <livewire:sctenant.card-iscpp-ignore :sctenant="$sctenant" />
    
    <!-- Halo Settings Tab -->
    @if(app(App\Settings\HaloServiceSettings::class)->enabled)
        <livewire:sctenant.card-halosettings :sctenant="$sctenant" />
    @else
        <flux:callout icon="information-circle">
            <flux.callout.text>
                Halo Service is disabled. Please enable it to configure settings.
            </flux.callout.text>
        </flux:callout>
    @endif
    
    <!-- Ninja Settings Tab -->
    @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
        <livewire:sctenant.card-ninjasettings :sctenant="$sctenant" />
    @else
        <flux:callout icon="information-circle">
            <flux.callout.text>
                Ninja Service is disabled. Please enable it to configure settings.
            </flux.callout.text>
        </flux:callout>
    @endif
</x-sctenants.show.layout>