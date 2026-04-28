<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="Endpoints" value="{{ $endpointsCount['all'] }}" />
    </div>

    <x-card title="Filter">
        <form action="{{ route('scendpoints.index') }}" method="GET" >
            <x-card-details-input label="Hostname" name="filterHostname" value="{{ request('filterHostname') }}" />
            <x-a-button type="submit">Filter</x-a-button>
        </form>
    </x-card>


    <x-card title="Endpoints">
        <x-scendpoints-table :scendpoints="$scendpoints" />
    </x-card>
</x-layouts.app>