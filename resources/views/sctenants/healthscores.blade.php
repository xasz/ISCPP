<x-layouts.app>
    <x-card title="Tenants">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th>Name</x-table.th>
                    <x-table.th>Endpoint Protection Computer</x-table.th>
                    <x-table.th>Endpoint Protection Server</x-table.th>
                    <x-table.th>Endpoint Policy Computer</x-table.th>
                    <x-table.th>Endpoint Policy Server</x-table.th>
                    <x-table.th>Endpoint Policy Computer</x-table.th>
                    <x-table.th>Endpoint Policy Server</x-table.th>
                    <x-table.th>Endpoint Policy Global</x-table.th>
                    <x-table.th>Endpoint Tamper Protection Computer</x-table.th>
                    <x-table.th>Endpoint Tamper Protection Server</x-table.th>
                    <x-table.th>Endpoint Tamper Protection Global</x-table.th>
                    <x-table.th>Endpoint MDR Data Telemetry</x-table.th>
                    <x-table.th>Endpoint MDR Contact</x-table.th>
                </tr>
            </x-table.thead>
            <tbody>
                @foreach ($sctenants as $sctenant)
                    <x-table.tr>
                        <x-table.td>
                                <x-table.a href="{{ route('sctenants.show', $sctenant) }}">
                                    {{ $sctenant->name }}
                                </x-table.a>
                                <br>
                                <span class="text-xs">{{ __('ShowAs: ') }} {{ $sctenant->showAs }} </span>
                        </x-table.td>  
                        @if($sctenant->SCTenantHealthscore == null)
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        <x-table.td></x-table.td>
                        @else
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointProtectionComputerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointProtectionServerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointPolicyComputerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointPolicyServerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsComputerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsServerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointExclusionsGlobalHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionComputerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionServerHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointTamperProtectionGlobalHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointMDRDataTelemetryProtectionImprovementHealthscore() }}</x-table.td>
                        <x-table.td>{{ $sctenant->SCTenantHealthscore->getEndpointMDRContactHealthscore() }}</x-table.td>
                        @endif  
                                
                    </x-table.tr>
                @endforeach
            </tbody>
        </x-table.table>
        <div class="py-4">
            {{ $sctenants->appends(request()->query())->links() }}
        </div>
    </x-card>
</x-layouts.app>