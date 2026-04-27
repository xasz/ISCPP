<x-scalerts.show.layout :scalert="$scalert">
    <x-card title="Json Data">
        <x-card-details-json :json="$scalert->rawData" />
    </x-card>
</x-scalerts.show.layout>
