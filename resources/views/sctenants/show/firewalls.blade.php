<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Firewalls">
        @if ($firewalls->isNotEmpty())
            <x-scfirewalls-table :scfirewalls="$firewalls" hideSCTenant=true />
            <div class="py-4">
                {{ $firewalls->links() }}
            </div>
        @else
            <flux:text>
                {{ __('No firewalls available for this tenant.') }}
            </flux:text>
        @endif
    </x-card>
</x-sctenants.show.layout>