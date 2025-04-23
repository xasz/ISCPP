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

    public ?bool $webhookBasicAuthEnabled = false;
    public ?string $webhookBasicAuthUsername = '';
    public ?string $webhookBasicAuthPassword = '';

    public ?bool $webhookEnabledHaloData = false;

    public $message;
    public $testResult;

    public function mount(WebhookSettings $settings)
    {
        $this->webhookEnabled = $settings->enabled;
        $this->webhookUrl = $settings->url;

        $this->webhookBasicAuthEnabled = $settings->basicAuthEnabled;
        $this->webhookBasicAuthUsername = $settings->basicAuthUsername;
        $this->webhookBasicAuthPassword = '';
        
        $this->webhookEnabledHaloData = $settings->enableHaloData;
        $this->updatePayload();
    }

    public function updated()
    {
        $this->message = "";
    }

    private function updatePayload(){
        $this->webhookPayload = json_encode(WebhookService::generatedPayload(SCAlert::latest()->first())) ?? '[]';
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
        $this->validate([
            'webhookUrl' => 'required|url',
        ]);
        
        if($this->webhookBasicAuthEnabled){
            $this->validate([
                'webhookBasicAuthUsername' => 'required|min:1|max:255',
                'webhookBasicAuthPassword' => 'required|min:1|max:255',
            ]);
        }

        $settings->basicAuthEnabled = $this->webhookBasicAuthEnabled;
        $settings->basicAuthUsername = $this->webhookBasicAuthUsername;
        $settings->basicAuthPassword = encrypt($this->webhookBasicAuthPassword);

        $settings->enabled = $this->webhookEnabled;
        $settings->enableHaloData = $this->webhookEnabledHaloData;
        $settings->url = $this->webhookUrl;
        $settings->save();

        $this->message = __('Saved');        
        $settings->basicAuthUsername = '';
        $this->updatePayload();
    }
    
}; ?>

<div>
    <x-card title="Webhook Alert Settings" subtitle="This will post a webhook with the specified payload">
        <div class="relative overflow-x-auto mt-2">
            <x-card-details-switch  label="Enable Webhooks to forward alerts to external systems." wire:model.live="webhookEnabled" />
            @if ($webhookEnabled)
                <x-card-details-input label="Url" wire:model="webhookUrl" errorModel="webhookUrl"/>
                <br>
                <x-card-details-switch label="Enable Additional Halo Data" wire:model.live="webhookEnabledHaloData"/>
                <flux:text>
                This will add additional halo specific data to the payload. This is only needed for sending Webhooks to Halo.
                </flux:text>
                <br>
                <x-card-details-switch label="Enable Basic Auth" wire:model.live="webhookBasicAuthEnabled"/>
                @if($webhookBasicAuthEnabled)
                    <x-card-details-input label="Basic Auth Username" wire:model.live="webhookBasicAuthUsername" errorModel="webhookBasicAuthUsername" />
                    <x-card-details-input label="Basic Auth Password" wire:model.live="webhookBasicAuthPassword" errorModel="webhookBasicAuthPassword"  />
                @endif
            @endif
            <div class="grid justify-items-end mt-4">
                <x-a-button wire:click="save">Save</x-a-button>
            </div>
            <div class="py-2">
                {{ $message }}
            <div>
        </div>
    </x-card>

    @if ($webhookEnabled)
    <x-card title="Test Webhook" subtitle="This will send a test webhook to the specified url.">        
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
    </x-card>
    @endif
</div>