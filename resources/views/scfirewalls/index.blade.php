<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="Firewalls" value="{{ $firewallsCount['all'] }}" />
    </div>
    <x-card title="Firewalls">
        <x-scfirewalls-table :scfirewalls="$scfirewalls" />
    </x-card>
</x-layouts.app>