<?php

namespace App\Jobs;

use App\Models\ISCPPEventDelivery;
use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Services\ISCPPWebhookSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Delivers one recorded event to one rule's webhook.
 *
 * Note there is deliberately no ShouldBeUniqueUntilProcessing here, unlike the other
 * jobs in this directory. Two deliveries for different events are distinct work and
 * uniqueness would silently drop them.
 *
 * Delivery is at least once: the event lives in the application database while the queue
 * is a separate connection, so a crash between a successful POST and writing the
 * delivery row will retry. Consumers must deduplicate on the Idempotency-Key header,
 * which carries the event uuid.
 */
class DeliverISCPPWebhook implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public function __construct(
        public int $eventId,
        public int $ruleId,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(ISCPPWebhookSender $sender): void
    {
        $event = ISCPPEventLog::find($this->eventId);
        $rule = ISCPPEventRule::find($this->ruleId);

        if (! $event || ! $rule) {
            return;
        }

        // Re-checked at execution time: opening the circuit breaker has to stop the
        // backlog that is already queued, not just prevent new deliveries.
        if (! $rule->enabled) {
            $this->record($event, $rule, ISCPPEventDelivery::STATUS_FAILED, null, __('Rule is disabled'));

            return;
        }

        try {
            $response = $sender->send($rule, $event);
        } catch (\Throwable $e) {
            $this->record($event, $rule, ISCPPEventDelivery::STATUS_FAILED, null, $e->getMessage());
            $this->retryOrFail($rule);

            return;
        }

        if ($response->successful()) {
            $this->record($event, $rule, ISCPPEventDelivery::STATUS_SUCCESS, $response->status(), null, $response->body());
            $rule->registerSuccess();

            return;
        }

        $this->record($event, $rule, ISCPPEventDelivery::STATUS_FAILED, $response->status(), null, $response->body());
        $this->retryOrFail($rule);
    }

    private function retryOrFail(ISCPPEventRule $rule): void
    {
        if ($this->attempts() >= $this->tries) {
            $rule->registerFailure();

            return;
        }

        $this->release($this->backoff()[$this->attempts() - 1] ?? 3600);
    }

    private function record(
        ISCPPEventLog $event,
        ISCPPEventRule $rule,
        string $status,
        ?int $httpStatus = null,
        ?string $error = null,
        ?string $body = null,
    ): void {
        // Never emit an ISCPP event from here. A delivery failure that raised an event
        // would feed itself.
        try {
            ISCPPEventDelivery::create([
                'iscpp_event_id' => $event->id,
                'iscpp_event_rule_id' => $rule->id,
                'attempt' => $this->attempts(),
                'status' => $status,
                'http_status' => $httpStatus,
                'url' => $rule->url,
                'response_body' => $body === null ? null : mb_substr($body, 0, 4096),
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            Log::error('ISCPP delivery could not be recorded', ['error' => $e->getMessage()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ISCPP webhook delivery failed permanently', [
            'event' => $this->eventId,
            'rule' => $this->ruleId,
            'error' => $e->getMessage(),
        ]);
    }
}
