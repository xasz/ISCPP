<?php

use App\Services\SCService;
use Livewire\Volt\Component;
use App\Models\SCBillable;
use Illuminate\Support\Str;

new class extends Component {

    public int $year;
    public int $month;
    public $billingData;
    public $errorMessage;
    public $message;

    public function mount()
    {
        $this->year = date('Y');
        $this->month = date('m');
    }

    private function preCheck(){

        $this->errorMessage = '';
        $this->message = '';
        if( SCBillable::where('year', $this->year)
            ->where('month', $this->month)
            ->count() > 0 ){
            $this->errorMessage = __('Data already exists - Import aborted');
            return false;
        }
        return true;
    }

    private function pushToDB(){
        foreach($this->billingData as $item){
            SCBillable::create([
                'year' => $this->year,
                'month' => $this->month,
                'tenantId' => $item['accountId'],
                'orderLineItemNumber' => $item['orderLineItemNumber'],
                'productGroup' => $item['productGroup'],
                'billableQuantity' => $item['billableQuantity'],
                'orderedQuantity' => $item['orderedQuantity'],
                'actualQuantity' => $item['actualQuantity'],
                'productCode' => $item['productCode'],
                'sku' => $item['sku'],
                'productDescription' => $item['productDescription'],
                'rawData' => json_encode($item),
            ]);
        }
        $this->message = count($this->billingData) . ' ' . __('billables imported');
    }

    public function fetchFake(SCService $scService)
    {
        if($this->preCheck() == false){
            return;
        }

        $this->billingData = $scService->fakebillingUsage($this->month, $this->year);

        $this->pushToDB();
    }    
    
    public function fetch(SCService $scService)
    {
        if($this->preCheck() == false){
            return;
        }

        try{
            $this->billingData = $scService->billingUsage($this->month, $this->year);

            $this->pushToDB();
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }    



}; ?>

<x-card>
    <flux:heading size="lg" level="1" class="mb-6">Sophos Central Billing Information Fetcher</flux:heading>    
    <div wire:loading.remove>
        <x-card-details-input label="Year" wire:model="year" />
        <x-card-details-input label="Month" wire:model="month" />
        @php
            /*
            <x-a-button  wire:click="fetchFake">Fetch Fake Data</x-a-button>
            */
        @endphp
        <x-a-button  wire:click="fetch">Fetch</x-a-button>
        @if($errorMessage)
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                {{ __($errorMessage) }}
            <div >
        @else
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                {{ __($message) }}
            </div>
        @endif
    </div>
    <div wire:loading>
        Loading ...
    </div>
</x-card>
