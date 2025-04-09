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
                $this->testResult = __('No credentials found');
                return;
            }

            $scService->initialize();
            $who = $scService->whoami();
            $this->testResult = collect($who)->toJson();

            $this->validated = ($who['idType'] ?? 'unkown') == 'partner';

        } catch (Exception $e) {
            $this->testResult = $e->getMessage();
            throw $e;
        }
    }

    public function runTenantRefresh(SCService $scService)
    {
        RefreshSCTenants::dispatch();
        $this->message2 = __('Tenant refresh triggered');
    }
    
}; ?>
<x-card>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Sophos Central Credentials') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Sophos Central API Credential Settings') }}
            </p>
        </header>
        
        <div>
            Please enter valid Sophos Central (Super Admin) Partner Credentials
        </div>
        <x-card-details-input label="Client ID" wire:model="clientId" />
        <x-card-details-input type="password" label="Client Secret" wire:model="clientSecret"/>
        
        <x-card>
            <div>
                Test the current credentials. (The one saved, not the one entered above)
            </div>
            <button type="button" wire:click="test" class="mb-4 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">Test</button>
            <x-card-details-json :json="$testResult" />
        </x-card>
        @if($validated)
            <div class="mt-8 pb-2">
                <div class="text-green">
                    {{ __('Partner Credentials validated. You can now use the Sophos Central API') }}
            </div>
        @endif
        <div class="grid justify-items-end mt-4">
            <x-a-button wire:click="save">Save</x-a-button>
        </div>
        <div>
            {{ $message }}
        <div>

        <hr>
        <div>
            <x-a-button wire:click="runTenantRefresh">Trigger Tenant Refresh (Automatically Done every 15 Minutes)</x-a-button>
        </div>
        <div>
            {{ $message2 }}
        <div>
    </section>
</x-card>
