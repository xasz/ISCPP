<x-sctenants.show.layout :sctenant="$sctenant">
    <x-card title="Json Data">
        <x-card-details-json :json="$sctenant->rawData" />
    </x-card>
</x-sctenants.show.layout>