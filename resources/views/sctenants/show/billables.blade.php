<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Billable Items">
        @if ($scbillables->isNotEmpty())
            <x-scbillables-table :scbillables="$scbillables" hideSCTenant=true />
        @else
            <flux:text>
                {{ __('No billables available for this tenant.') }}
            </flux:text>
        @endif
    </x-card>
</x-sctenants.show.layout>