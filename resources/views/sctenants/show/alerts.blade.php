<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Tenant Alerts">
        <x-scalerts-table :scalerts="$scalerts" hideSCTenant=true/>
    </x-card>
</x-sctenants.show.layout>