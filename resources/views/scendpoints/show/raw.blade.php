<x-scendpoints.show.layout :endpoint="$endpoint">
    <x-card title="Json Data">
        <x-card-details-json :json="$endpoint->rawData" />
    </x-card>
</x-scendpoints.show.layout>
