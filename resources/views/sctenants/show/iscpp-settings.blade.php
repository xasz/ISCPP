<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="ISCPP Settings"> 
        <!-- Halo Settings Tab -->
        @if(app(App\Settings\HaloServiceSettings::class)->enabled)
        <x-tab-panel name="halo">
            <livewire:sctenant.card-halosettings :sctenant="$sctenant" />
        </x-tab-panel>
        @endif
        <!-- Halo Settings Tab -->
        @if(app(App\Settings\NinjaServiceSettings::class)->enabled)
        <x-tab-panel name="ninja">
            <livewire:sctenant.card-ninjasettings :sctenant="$sctenant" />
        </x-tab-panel>
        @endif
    </x-card>
</x-sctenants.show.layout>