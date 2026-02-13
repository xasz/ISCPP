<?php

namespace App\Observers;

use App\Models\SCAlert;
use App\Models\SCAlertAutoAction;
use App\Actions\SCAlertAcknowledgeAction;
use App\Models\Event;

class SCAlertAutoAcknowledgeObserver
{

    protected SCAlertAcknowledgeAction $action;
    public function __construct(SCAlertAcknowledgeAction $action)
    {
        $this->action = $action;
    }
    /**
     * Handle the SCAlert "created" event for auto-acknowledgement.
     *
     * @param  \App\Models\SCAlert  $scalert
     * @return void
     */
    public function created(SCAlert $scalert)
    {
        Event::logDebug('scalerts', 'Auto-Acknowledge started completed for alert ID: ' . $scalert->id);
        $this->acknowledge($scalert);
        Event::logDebug('scalerts', 'Auto-Acknowledge check completed for alert ID: ' . $scalert->id);
    }

    public function updated(SCAlert $scalert)
    {
        Event::logDebug('scalerts', 'Update triggered for alert ID: ' . $scalert->id);
        if ($scalert->is_acknowledged) {
            return;
        }
        $this->acknowledge($scalert);
    }

    
    protected function acknowledge(SCAlert $scalert)
    {
        Event::logInfo('scalerts', 'SC Alert Auto-Acknowledge triggered for alert ID: ' . $scalert->id);
        
        $autoAction = SCAlertAutoAction::where('type', $scalert->type)
        ->where('action', 'AutoAcknowledge')
        ->first();
        
        if($autoAction) {
            if (collect($scalert->allowedActions)->contains('acknowledge')) {
                $this->action->execute($scalert);
            }else{
                Event::logWarning('scalerts', 'SC Alert Auto-Acknowledge not allowed for alert ID: ' . $scalert->id);
            }
        }
        
    }

}
