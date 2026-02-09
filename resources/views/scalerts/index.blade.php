
<x-layouts.app>
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">            
        <x-card-simple-info title="All (24h)" value="{{ $alertsCount['all'] }}" />
        <x-card-simple-info title="Low (24 h)" value="{{ $alertsCount['low'] }}" />
        <x-card-simple-info title="Medium (24h)" value="{{ $alertsCount['medium'] }}" />
        <x-card-simple-info title="High (24h)" value="{{ $alertsCount['high'] }}" />
    </div>

    <x-card>
        <canvas class="w-full h-64" id="scalerts-chart">
            </canvas>
            @push('scripts')
            <script>

            data = {
                labels: @json($chartData->map(fn ($data) => $data['date'])),
                datasets: [{
                    label: 'Registered alerts in the last 30 days',
                    backgroundColor: 'rgba(255, 99, 132, 0.3)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: @json($chartData->map(fn ($data) => $data['aggregate'])),
                }]
            };

            config = {
                type: 'line',
                data: data,
                options: {
                    maintainAspectRatio: false,
                }
            };

            myChart = new Chart(
                document.getElementById('scalerts-chart'),
                config
            );
            </script>
            @endpush
    </x-card>


    <x-card title="Filter">
        <form action="{{ route('scalerts.index') }}" method="GET" >
            <div class="flex items-center gap-4">
                <x-card-details-switch label="{{ __('Hide Acknowledged') }}" name="hide_acknowledged" :checked="request('hide_acknowledged') === '1'" value="1" />

                <div class="flex items-center gap-2" id="severity-switches">
                    <span class="text-sm font-medium">Severity:</span>
                    @php
                        $noneSet = request()->missing('severity_low') && request()->missing('severity_medium') && request()->missing('severity_high');
                    @endphp
                    <x-card-details-switch label="Low" name="severity_low" value="1" :checked="$noneSet || request('severity_low') === '1'" />
                    <x-card-details-switch label="Medium" name="severity_medium" value="1" :checked="$noneSet || request('severity_medium') === '1'" />
                    <x-card-details-switch label="High" name="severity_high" value="1" :checked="$noneSet || request('severity_high') === '1'" />
                </div>

                <x-a-button type="submit">Filter</x-a-button>
            </div>
            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const switches = document.querySelectorAll('#severity-switches flux\\:switch');
                    const hidden = document.getElementById('severity-hidden');
                    function updateHidden() {
                        let selected = [];
                        switches.forEach(sw => {
                            if (sw.checked) selected.push(sw.value);
                        });
                        hidden.value = selected.join(',');
                    }
                    switches.forEach(sw => {
                        sw.addEventListener('change', updateHidden);
                    });
                });
            </script>
            @endpush
        </form>
    </x-card>

    <x-card>
        <x-scalerts-table :scalerts="$scalerts" />
    </x-card>
</x-layouts.app>