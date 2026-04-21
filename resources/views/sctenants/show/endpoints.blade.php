<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Endpoints">
        @if ($endpoints->isNotEmpty())
            <x-scendpoints-table :scendpoints="$endpoints" hideSCTenant=true />
            <div class="py-4">
                {{ $endpoints->links() }}
            </div>
        @else
            <flux:text>
                {{ __('No endpoints available for this tenant.') }}
            </flux:text>
        @endif
    </x-card>
</x-sctenants.show.layout>