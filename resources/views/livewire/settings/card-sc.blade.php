<?php

use App\Services\APICrendentialService;
use App\Services\SCService;
use Livewire\Volt\Component;
use App\Jobs\RefreshSCTenants;
use App\Settings\SCServiceSettings;


new class extends Component {
    public $clientId;
    public $clientSecret;

    public $message;

    public $testResult;
    public $testResultMessage;


    public $validated = false;

    
    public function mount(SCServiceSettings $settings)
    {
        $this->clientSecret = null;
        $this->clientId = $settings->clientId;
    }


    public function save(SCServiceSettings $settings)
    {
        if($this->clientSecret == null){
            $this->message = __('Client Secret is required');
            return;
        }
        
        if($this->clientId == null){
            $this->message = __('Client ID is required');
            return;
        }
        $settings->clientSecret = encrypt($this->clientSecret);
        $settings->clientId = $this->clientId;
        $settings->token_expires_at = time();
        $settings->token = null;
        $settings->refresh_token = null;
        $settings->save();
        
        $this->clientSecret = '';
        $this->message = __('Saved');

        $this->dispatch('featureSet-changed');
    }

    public function test(SCService $scService)
    {
        try{
            $who = $scService->whoami();
            $this->testResult = collect($who)->toJson();
            $this->validated = ($who['idType'] ?? 'unkown') == 'partner';
            $this->testResultMessage = __('Credentials are valid');
        } catch (Exception $e) {
            $this->testResultMessage = $e->getMessage();
        }
    }
    
}; ?>

<div class="grid grid-cols-2 w-full gap-3">
    <x-card class="w-1/2" title="Sophos Central Credentials" subtitle="SPlease enter valid Sophos Central (Super Admin) Partner Credentials">
            <x-card-details-input label="Client ID" wire:model="clientId" />
            <x-card-details-input type="password" label="Client Secret" wire:model="clientSecret"/>
            <div class="grid justify-items-end mt-4">
                <x-a-button wire:click="save">Save</x-a-button>
            </div>
            <flux:text>
                {{ $message }}
            </flux:text>
            <x-card-hr/>
            <flux:text>
                {{ __('Credentials are used to fetch data from Sophos Central. Please make sure you have the correct permissions set in Sophos Central.') }}
            </flux:text>
    </x-card>

    <x-card class="w-1/2" title="Test Sophos Central Credentials" subtitle="Test the current credentials (Please hit save first, when you entered new credentials)">
        <div class="space-y-4">
            <div wire:loading>
                <flux:text>
                    {{ __('Testing...') }}
                </flux:text>
            </div>
            <div wire:loading.remove>
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
        </div>
    </x-card>
</div>