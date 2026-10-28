<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Endpoints" subtitle="{{ $endpoints->total() }} {{ __('total') }}">
        <x-scendpoints-table :scendpoints="$endpoints" hideSCTenant=true />
    </x-card>
</x-sctenants.show.layout>
