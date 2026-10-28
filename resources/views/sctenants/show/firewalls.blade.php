<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Firewalls" subtitle="{{ $firewalls->total() }} {{ __('total') }}">
        <x-scfirewalls-table :scfirewalls="$firewalls" hideSCTenant=true />
    </x-card>
</x-sctenants.show.layout>
