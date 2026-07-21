<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\SCAlert;
use App\Services\SCService;

class SCAlertAcknowledgeAction
{
    public function execute(SCAlert $scalert): void
    {
        
        Event::log('scalerts', 'info', [
            'message' => 'SC Alert Acknowledge triggered',
            'SCAlert' => $scalert->id,
        ]);
        try {
            app(SCService::class)->alertsAction($scalert->SCTenant, $scalert, 'acknowledge', 'Auto-acknowledged by ISCPP');
            $scalert->is_acknowledged = true;
            $scalert->save();
        } catch (\Throwable $e) {
            Event::throwError('scalerts', 'SC Alert Acknowledge failed: ' . $e->getMessage());
        }
    }
}