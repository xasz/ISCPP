<?php

use Livewire\Volt\Component;
use App\Models\SCTenant;
use App\Jobs\PushToHaloSCTenantSCBillables;

new class extends Component {


    
    public int $year;
    public int $month;


    public $errorMessage;
    public $message;
    
    public function SCTenants()
    {
        return SCTenant::whereHas('SCBillables', function($query){
            $query->where('year', $this->year)
                ->where('month', $this->month);
        })
        ->withCount(['SCBillables as not_sent_to_halo' => function ($query) {
            $query->where('year', $this->year)
                ->where('month', $this->month)
                ->whereNot('sent_to_halo', 'success');
        }], 'id')

        ->withCount(['SCBillables as sent_to_halo' => function ($query) {
            $query->where('year', $this->year)
                ->where('month', $this->month)
                ->where('sent_to_halo', 'success');
        }], 'id')
        ->get();
    }

    public function SCTenantsValidatation(){
        return $this->SCTenants()->map(function($tenant){
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'haloclient_id' => $tenant->haloclient_id,
                'has_haloclient_id' => $tenant->haloclient_id > 0,
                'not_sent_to_halo' => $tenant->not_sent_to_halo,
                'sent_to_halo' => $tenant->sent_to_halo,
            ];
        });
    }

    public function mount()
    {
        $this->year = date('Y');
        $this->month = date('m');       
    }


    public function push(){
        $this->errorMessage = '';
        $this->message = '';

        $sctenants = $this->SCTenantsValidatation();
        if($sctenants->where('has_haloclient_id', false)->count() > 0){
            $this->errorMessage = __('Some tenants do not have Halo Client ID');
            return;
        }      

        foreach($sctenants as $sctenant){
            PushToHaloSCTenantSCBillables::dispatch($sctenant['id'], $this->year, $this->month);
        }
        $this->message = $this->year . '/' . $this->month . 'Pushed SCBillables to Halo';

    }
    public function pushSCTenant($sctenantID){

        $this->errorMessage = '';
        $this->message = '';
        $sctenant = SCTenant::find($sctenantID);
        if($sctenant->haloclient_id  > 0){
            $this->errorMessage = __('SCTenant do not have Halo Client ID');
            return;
        }  
        
        PushToHaloSCTenantSCBillables::dispatch($sctenant, $this->year, $this->month);
        $this->message = $this->year . '/' . $this->month . 'Pushed ' . $sctenant->name . ' SCBillables to Halo';
    }

}; ?>

<x-card>
    <flux:heading size="lg" level="1" class="mb-6">Halo Billing Information Pusher</flux:heading>    
        
    <x-card-details-input label="Year" :value="$year" />
    <x-card-details-input label="Month" :value="$month" />

    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Name</x-table.th>
                <x-table.th>Halo Client ID</x-table.th>
                <x-table.th>Valid</x-table.th>
                <x-table.th>Unpushed SCBillables</x-table.th>
                <x-table.th>Pushed SCBillables</x-table.th>
                <x-table.th>Actions</x-table.th>
            </tr>
        </x-table.thead>
        <tbody>
            @foreach ($this->SCTenantsValidatation() as $scTenant)
            <x-table.tr>
                <x-table.td>
                    <x-table.a href="{{ route('sctenants.show', $scTenant['id']) }}">
                        {{ $scTenant['name'] }}
                    </x-table.a>
                </x-table.td>
                <x-table.td>
                    {{ $scTenant['haloclient_id'] }}
                </x-table.td>
                <x-table.td>
                    @if($scTenant['has_haloclient_id'])
                        {{ __('Yes') }}
                    @else
                        {{ __('No') }}
                    @endif
                </x-table.td>
                <x-table.td>
                    {{ $scTenant['not_sent_to_halo'] }}
                </x-table.td>
                <x-table.td>
                    {{ $scTenant['sent_to_halo'] }}
                </x-table.td>
                <x-table.td>
                    @if($scTenant['has_haloclient_id'] == true)
                    <x-a-button wire:click="pushSCTenant('{{ $scTenant['id'] }}')">
                        {{ __('Queue Push') }}
                    </x-a-button>
                    @endif
                </x-table.td>
            </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>

    <x-a-button wire:click="push">
        {{ __('Start Queue Push All!') }}
    </x-a-button>
    
    @if($errorMessage)
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
            {{ __($errorMessage) }}
        <div >
    @else
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            {{ __($message) }}
        </div>
    @endif
</x-card>
