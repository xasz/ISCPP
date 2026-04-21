<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Billable Items"> 
        <x-scbillables-table :scbillables="$scbillables" hideSCTenant=true />
    </x-card>
</x-sctenants.show.layout>