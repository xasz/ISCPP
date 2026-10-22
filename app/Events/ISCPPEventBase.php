<?php

namespace App\Events;

use App\Models\SCTenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Str;

/**
 * Base class for every ISCPP event.
 *
 * To add a new event, create one file extending this class and implement the three
 * abstract methods. Nothing else is required: no registration, no config entry and
 * deliberately no constructor contract, so subclasses are free to use promoted
 * constructor properties.
 */
abstract class ISCPPEventBase implements ISCPPEvent
{
    use Dispatchable;

    private ?string $uuid = null;

    private ?CarbonImmutable $occurredAt = null;

    /**
     * Stable machine key used in the database, in rules and in the JSON payload.
     * Never change this once it has shipped.
     */
    abstract public static function key(): string;

    /**
     * Human readable label. Resolved at display time, so __() is safe here.
     */
    abstract public static function label(): string;

    /**
     * The event specific attributes. This is the only part a new event has to think about.
     *
     * @return array<string, mixed>
     */
    abstract public function data(): array;

    /**
     * Severity of this event type. Shown in the event catalog and used by default.
     *
     * Override this for a fixed severity. Override severity() instead only when the
     * severity depends on the individual event, as the system log bridge does.
     */
    public static function defaultSeverity(): ISCPPEventSeverity
    {
        return ISCPPEventSeverity::Info;
    }

    /**
     * Severity of this particular event occurrence.
     */
    public function severity(): ISCPPEventSeverity
    {
        return static::defaultSeverity();
    }

    /**
     * One or two sentences for the event catalog: when this event is raised.
     */
    public static function description(): string
    {
        return '';
    }

    /**
     * The tenant this event belongs to, or null for a global event.
     */
    public function tenant(): ?SCTenant
    {
        return null;
    }

    /**
     * The record this event is about, used to build deep links in the event log.
     */
    public function subject(): ?Model
    {
        return null;
    }

    /**
     * Payload paths offered as suggestions in the rule condition builder.
     *
     * @return array<string, string> path => label
     */
    public static function filterablePaths(): array
    {
        return [];
    }

    /**
     * Sample attributes used to render an example payload in the rule builder.
     *
     * Optional. Without it the example still renders, just with an empty data block.
     *
     * @return array<string, mixed>
     */
    public static function exampleData(): array
    {
        return [];
    }

    final public function uuid(): string
    {
        return $this->uuid ??= (string) Str::uuid7();
    }

    final public function occurredAt(): CarbonImmutable
    {
        return $this->occurredAt ??= CarbonImmutable::now();
    }
}
