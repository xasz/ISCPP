<?php

use App\Services\APICrendentialService;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Jobs\RefreshSCTenants;


new class extends Component {
    public $clientId;
    public $clientSecret;

    public $message;
    public $message2;

    public $testResult;
    public $testResultMessage;


    public $validated = false;

    
    public function mount()
    {
        $cred = APICrendentialService::new()->getCredentials('sc');
        if($cred != null){
            $this->clientId = $cred->clientid;
            $this->clientSecret = '';
        }
    }


    public function save()
    {
        APICrendentialService::new()->saveCredentials('sc', $this->clientId, $this->clientSecret);
        $this->clientSecret = '';
        $this->clientId = __('Value saved');
        $this->message = $this->clientId;
    }

    public function test(SCService $scService)
    {
        try{
            $cred = APICrendentialService::new()->getCredentials('sc');
            if($cred == null){
                $this->testResultMessage = __('No credentials found');
                return;
            }

            $scService->initialize();
            $who = $scService->whoami();
            $this->testResult = collect($who)->toJson();

            $this->validated = ($who['idType'] ?? 'unkown') == 'partner';
            
            $this->testResultMessage = __('Credentials are valid');
        } catch (Exception $e) {
            $this->testResultMessage = $e->getMessage();
        }
    }

    public function runTenantRefresh(SCService $scService)
    {
        RefreshSCTenants::dispatch();
        $this->message2 = __('Tenant refresh triggered');
    }
    
}; ?>

<x-card title="Sophos Central Credentials" subtitle="SPlease enter valid Sophos Central (Super Admin) Partner Credentials">
        
        <x-card-details-input label="Client ID" wire:model="clientId" />
        <x-card-details-input type="password" label="Client Secret" wire:model="clientSecret"/>
        <div class="grid justify-items-end mt-4">
            <x-a-button wire:click="save">Save</x-a-button>
        </div>
        
        <x-card-hr/>

        <x-subcard>
            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Test the current credentials (The one saved, not the one entered above):') }}
                </p>
                <x-a-button wire:click="test">
                    {{ __('Test Credentials') }}
                </x-a-button>
                @if($testResult)
                    <x-card-details-json :json="$testResult" />
                @endif
                @if($testResultMessage)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $testResultMessage }}
                    </div>
                @endif
            </div>
        </x-subcard>
        <div>
            {{ $message }}
        <div>
        <x-card-hr/>

        <div>
            <x-a-button wire:click="runTenantRefresh">Trigger Tenant Refresh (Automatically Done every 15 Minutes)</x-a-button>
        </div>
        <div>
            {{ $message2 }}
        <div>
</x-card>
