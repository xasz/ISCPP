
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

    <x-card>
        <x-scalerts-table :scalerts="$scalerts" />
    </x-card>
</x-layouts.app>