<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\SCAlert;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSCAlertWebhook implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public $tries = 5;

    public function __construct(
        public SCAlert $scalert,
    ) {
        $this->scalert = $scalert->withoutRelations();
    }

    /**
     * Execute the job.
     */
    public function handle(WebhookService $wService): void
    {
        Event::log("webhook", "info" , [
            'message' => 'Start sending webhook for SCAlert ',
            'SCalertID' => $this->scalert->id]
        );

        $response = $wService->runWebhook($this->scalert);

        if($response != null && $response->successful()){
            $this->scalert->update(['webhook_sent' => 'success']);
            $this->scalert->save();
        }else{
            if($this->scalert->webhookLog()->count() >= 3){
                $this->scalert->update(['webhook_sent' => 'failed']);
                $this->scalert->save();
                
                Event::log("webhook", "error" , [
                    'message' => __('Webhook failed - no more retry'),
                    'SCalertID' => $this->scalert->id]
                );
                return;
            }
            Event::log("webhook", "warning" , [
                'message' => __('Webhook failed - retrying in 120 seconds'),
                'SCalertID' => $this->scalert->id]
            );
            $this->release(120);
        }
        
    }

    public function uniqueId(): string
    {
        return $this->scalert->id;
    }
}
