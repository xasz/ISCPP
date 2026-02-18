<?php

namespace App\Observers;

use App\Jobs\SendSCAlertWebhook;
use App\Models\Event;
use App\Models\SCAlert;
use App\Models\SCAlertAutoAction;
use App\Services\SCService;
use App\Settings\WebhookSettings;

class SCAlertObserver
{

    /**
     * Handle the SCAlert "created" event.
     *
     * @param  \App\Models\SCAlert  $scalert
     * @return void
     */
    public function created(SCAlert $scalert)
    {
        if(app(WebhookSettings::class)->enabled){
            $action = SCAlertAutoAction::where('type', $scalert->type)
            ->where('action', 'SuppressWebhook')
            ->first();

            if ($action) {
                Event::logDebug('scalerts', 'Webhook suppressed for alert ID: ' . $scalert->id);
                return;
            }
            $scalert->dispatchWebhook();
        }
    }
}
