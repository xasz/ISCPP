<?php

namespace App\Listeners;

use App\Events\ISCPPEvent;
use App\Events\ISCPPEventBase;
use App\Jobs\DeliverISCPPWebhook;
use App\Models\Event;
use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Services\ISCPPEvents;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Records every ISCPP event and queues the deliveries it matches.
 *
 * Registered once against the ISCPPEvent interface, which is why that marker has to be
 * an interface: Laravel resolves listeners through class_implements(), so a listener
 * bound to a parent class would never fire for its subclasses.
 *
 * Rules are matched in process rather than in a queued job. One queued job per event
 * would mean a job per imported alert on the queue; this way a job
 * is only queued when a delivery is actually going to happen.
 */
class RecordISCPPEvent
{
    public function record(ISCPPEvent $event): void
    {
        // This listener runs first in the SCAlert observer chain, so an exception here
        // would abort auto-acknowledge and the legacy webhook. New code must never be
        // able to break either of those.
        try {
            if (! $event instanceof ISCPPEventBase) {
                return;
            }

            if (! config('iscpp.events.enabled', true) || ISCPPEvents::muted()) {
                return;
            }

            $tenant = $event->tenant();

            if ($tenant !== null && $this->isIgnored($tenant)) {
                return;
            }

            $row = ISCPPEventLog::create($this->attributes($event, $tenant));

            // each() discards the PendingDispatch, so its destructor (and therefore the
            // afterCommit dispatch) runs inside this try block. Do not swap this for a
            // map() whose result is retained.
            ISCPPEventRule::cachedEnabled()
                ->filter(fn (ISCPPEventRule $rule) => $rule->matches($row))
                ->each(fn (ISCPPEventRule $rule) => DeliverISCPPWebhook::dispatch($row->id, $rule->id)->afterCommit());
        } catch (\Throwable $e) {
            $this->reportFailure($event, $e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(ISCPPEventBase $event, ?SCTenant $tenant): array
    {
        $subject = $event->subject();

        return [
            'uuid' => $event->uuid(),
            'key' => $event::key(),
            'severity' => $event->severity(),
            'tenantId' => $tenant?->getKey(),
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'payload' => $this->envelope($event, $tenant, $subject),
            'occurred_at' => $event->occurredAt(),
        ];
    }

    /**
     * The JSON representation forwarded to webhooks.
     *
     * Within payloadVersion 1 fields are only ever added, never removed or retyped.
     *
     * @return array<string, mixed>
     */
    private function envelope(ISCPPEventBase $event, ?SCTenant $tenant, mixed $subject): array
    {
        return [
            'iscpp' => [
                'payloadVersion' => 1,
                'instance' => config('app.url'),
            ],
            'event' => [
                'id' => $event->uuid(),
                'key' => $event::key(),
                'label' => $event::label(),
                'severity' => $event->severity()->value,
                'occurredAt' => $event->occurredAt()->toIso8601String(),
            ],
            'tenant' => $tenant === null ? null : [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'showAs' => $tenant->showAs,
                'haloclientId' => $tenant->haloclient_id,
                'ninjaorgId' => $tenant->ninjaorg_id,
            ],
            'subject' => $subject === null ? null : [
                'type' => class_basename($subject),
                'id' => $subject->getKey(),
            ],
            'data' => $event->data(),
        ];
    }

    /**
     * Ignored tenants are excluded from ISCPP entirely, so "all tenants" on a rule means
     * all tenants that are not ignored.
     *
     * Resolved against a short lived set rather than $event->tenant()->iscpp_ignore,
     * which would be one extra query per imported alert. The TTL is deliberate: the
     * array driver in a long lived queue worker otherwise caches for the process life.
     */
    private function isIgnored(SCTenant $tenant): bool
    {
        if ($tenant->iscpp_ignore) {
            return true;
        }

        return Cache::driver('array')
            ->remember('iscpp:ignored', 60, fn () => SCTenant::where('iscpp_ignore', true)->pluck('id')->flip())
            ->has($tenant->getKey());
    }

    private function reportFailure(ISCPPEvent $event, \Throwable $e): void
    {
        $key = $event instanceof ISCPPEventBase ? $event::key() : $event::class;

        // File based, so it survives the database being the thing that failed.
        Log::error('ISCPP event emit failed', ['key' => $key, 'error' => $e->getMessage()]);

        // The legacy log is a database write, and the database is the most likely cause
        // of the failure being handled, so it must not be able to rethrow from here.
        try {
            Event::log('iscpp', 'error', [
                'message' => 'ISCPP event emit failed: '.$e->getMessage(),
                'key' => $key,
            ]);
        } catch (\Throwable) {
            // Nothing further we can safely do.
        }
    }
}
