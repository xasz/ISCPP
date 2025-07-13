<?php

use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;
use App\Settings\NinjaServiceSettings;
use App\Services\NinjaService;

new class extends Component {
    
    public $enabled;
    public $instance;
    public $scope;
    public $clientSecret;
    public $clientId;

    public $autoPushCentralEndpointInstallerUrl;
    public $windowsSophosCentralEndpointInstallerUrl;
    public $linuxSophosCentralEndpointInstallerUrl;
    public $macSophosCentralEndpointInstallerUrl;

    public $message;
    public $testResult;

    public function mount(NinjaServiceSettings $settings)
    {
        $this->enabled = $settings->enabled;
        $this->instance = $settings->instance;
        $this->scope = $settings->scope;
        $this->clientSecret = null;
        $this->clientId = $settings->clientId;

        $this->autoPushCentralEndpointInstallerUrl = $settings->autoPushCentralEndpointInstallerUrl;
        $this->windowsSophosCentralEndpointInstallerUrl = $settings->windowsSophosCentralEndpointInstallerUrl;
        $this->linuxSophosCentralEndpointInstallerUrl = $settings->linuxSophosCentralEndpointInstallerUrl;
        $this->macSophosCentralEndpointInstallerUrl = $settings->macSophosCentralEndpointInstallerUrl;
    }

    public function test(NinjaService $nService)
    {
        try{
            $nService->test();
            $this->testResult = __("Ninja connection successfull");
        } catch (Exception $e) {
            $this->testResult = $e->getMessage();
        }
    }

    public function save(NinjaServiceSettings $settings)
    {
        if($this->enabled == false){
            $settings->enabled = false;
            $settings->save();
            $this->message = __('Disabled');
            return;
        }

        if($this->clientSecret == null){
            $this->message = __('Client Secret is required');
            return;
        }
        if($this->clientId == null){
            $this->message = __('Client ID is required');
            return;
        }
        

        $settings->enabled = $this->enabled;
        $settings->instance = $this->instance;
        $settings->scope = $this->scope;
        $settings->clientSecret = encrypt($this->clientSecret);
        $settings->clientId = $this->clientId;

        $settings->autoPushCentralEndpointInstallerUrl = $this->autoPushCentralEndpointInstallerUrl;
        $settings->windowsSophosCentralEndpointInstallerUrl = $this->windowsSophosCentralEndpointInstallerUrl;
        $settings->linuxSophosCentralEndpointInstallerUrl = $this->linuxSophosCentralEndpointInstallerUrl;
        $settings->macSophosCentralEndpointInstallerUrl = $this->macSophosCentralEndpointInstallerUrl;

        $settings->save();
        $this->clientSecret = '';

        $this->message = __('Saved');        
        $this->dispatch('featureSet-changed');
    }
    
}; ?>

<x-card title="NinjaOne Service Settings (Preview Only)" subtitle="This will be used for injecting Data to NinjaOne">
        <div class="relative overflow-x-auto mt-2">
            <x-card-details-switch  label="Enable Ninja Deployment Integration" wire:model.live="enabled" />
            @if ($enabled)
                <x-card-details-input label="Instance Name" wire:model="instance"/>
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

                <x-card-details-switch label="Auto Push Central Endpoint Installer" wire:model.live="autoPushCentralEndpointInstallerUrl" />
                <x-card-details-input label="Windows Sophos Central Endpoint Installer URL" wire:model="windowsSophosCentralEndpointInstallerUrl"/>
                <x-card-details-input label="Linux Sophos Central Endpoint Installer URL" wire:model="linuxSophosCentralEndpointInstallerUrl"/>
                <x-card-details-input label="Mac Sophos Central Endpoint Installer URL" wire:model="macSophosCentralEndpointInstallerUrl"/>
                
            @endif
            <div class="grid justify-items-end mt-4">
                <x-a-button wire:click="save">{{ __('Save') }}</x-a-button>
            </div>
            <div class="py-2">
                {{ $message }}
            <div>
        </div>
</x-card>
