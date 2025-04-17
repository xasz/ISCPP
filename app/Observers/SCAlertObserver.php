<?php

namespace App\Observers;

use App\Jobs\SendSCAlertWebhook;
use App\Models\SCAlert;
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
            $scalert->dispatchWebhook();
        }
    }
}
