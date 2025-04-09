<?php

use App\Jobs\SendSCAlertWebhook;
use App\Models\SCAlert;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;
use App\Settings\WebhookSettings;

new class extends Component {
    
    public $webhookEnabled;
    public $webhookUrl;
    public $webhookPayload;
    public $webhookheader;

    public $message;
    public $testResult;

    public function mount(WebhookSettings $settings)
    {
        $this->webhookEnabled = $settings->enabled;
        $this->webhookUrl = $settings->url;
        
        $this->webhookheader = $settings->header;
        $this->webhookPayload = SCAlert::latest()->first()->rawData ?? '[]';
    }

    public function test(WebhookService $wService)
    {
        try{

            $alert = $this->webhookPayload = SCAlert::latest()->first();
            $this->webhookPayload = $alert->rawData ?? '[]';
            
            $webhook = new SendSCAlertWebhook($alert);
            $webhook->handle($wService);

            $this->testResult = __("Webhook sent");
        } catch (Exception $e) {
            $this->testResult = $e->getMessage();
        }
    }

    public function save(WebhookSettings $settings)
    {
        $settings->enabled = $this->webhookEnabled;
        $settings->url = $this->webhookUrl;
        $settings->header = $this->webhookUrl;
        $settings->save();
        $this->message = __('Saved');        
    }
    
}; ?>

<x-card title="Webhook Alert Settings" subtitle="This will post a webhook with the specified payload">
    <div class="relative overflow-x-auto mt-2">
        <x-card-details-switch  label="Enable Webhooks to forward alerts to external systems." wire:model.live="webhookEnabled" />
        @if ($webhookEnabled)
            <x-card-details-input label="Url" wire:model="webhookUrl"/>
            <x-card-details-input label="header" wire:model="webhookHeader"/>
            <div class="py-2">
                {{ $message }}
            <div>
            <x-subcard>        
                <div>
                    <h2>
                    {{ __('Test the current credentials. (The one saved, not the one entered above)') }}
                    </h2>
                    <x-card-details-json :json="$this->webhookPayload"/>
                    <x-a-button wire:click="test">Test</x-a-button>
                    <div>
                        {{ $testResult }}
                    </div>
                </div>
            </x-subcard>
        @endif
        <div class="grid justify-items-end mt-4">
            <x-a-button wire:click="save">Save</x-a-button>
        </div>
    </div>
</x-card>
