<?php

use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;
use App\Settings\HaloServiceSettings;
use App\Services\HaloService;

new class extends Component {
    
    public $enabled;
    public $instance;
    public $url;
    public $scope;
    public $clientSecret;
    public $clientId;

    public $message;
    public $testResult;

    public function mount(HaloServiceSettings $settings)
    {
        $this->enabled = $settings->enabled;
        $this->instance = $settings->instance;
        $this->url = $settings->url;
        $this->scope = $settings->scope;
        $this->clientSecret = null;
        $this->clientId = $settings->clientId;
    }

    public function test(HaloService $hService)
    {
        try{
            $hService->test();
            $this->testResult = __("Halo connection successfull");
        } catch (Exception $e) {
            $this->testResult = $e->getMessage();
        }
    }

    public function save(HaloServiceSettings $settings)
    {
        if($this->clientSecret == null){
            $this->message = __('Client Secret is required');
            return;
        }

        $settings->enabled = $this->enabled;
        $settings->instance = $this->instance;
        $settings->url = $this->url;
        $settings->scope = $this->scope;
        $settings->clientSecret = encrypt($this->clientSecret);
        $settings->clientId = $this->clientId;
        $settings->save();
        $this->clientSecret = '';

        $this->message = __('Saved');        
    }
    
}; ?>

<x-card title="Halo Service Settings" subtitle="This will be used for injecting Data to Halo">
        <div class="relative overflow-x-auto mt-2">
            <x-card-details-switch  label="Enable Halo billing Integration" wire:model.live="enabled" />
            @if ($enabled)
                <x-card-details-input label="Instance Name" wire:model="instance"/>
                <x-card-details-input label="Instance Url" wire:model="url"/>

                <x-card-details-input label="scope" wire:model="scope"/>
                <x-card-details-input label="clientId" wire:model="clientId"/>
                <x-card-details-input type="password" label="Client Secret" wire:model="clientSecret"/>
                <x-subcard>         
                    <div>
                        <h2>
                        {{ __('Test the current credentials. (The one saved, not the one entered above)') }}
                        </h2>
                        <x-a-button wire:click="test">{{ __('Test')}}</x-a-button>
                        <div>
                            {{ $testResult }}
                        </div>
                    </div>
                </x-subcard>
            @endif
            <div class="grid justify-items-end mt-4">
                <x-a-button wire:click="save">{{ __('Save') }}</x-a-button>
            </div>
            <div class="py-2">
                {{ $message }}
            <div>
        </div>
</x-card>
