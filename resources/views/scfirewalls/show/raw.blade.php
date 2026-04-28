<x-scfirewalls.show.layout :firewall="$firewall">
    <x-card title="Json Data">
        <x-card-details-json :json="$firewall->rawData" />
    </x-card>
</x-scfirewalls.show.layout>
