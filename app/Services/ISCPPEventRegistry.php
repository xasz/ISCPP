<?php

namespace App\Services;

use App\Events\ISCPPEventBase;
use App\Events\ISCPPEventSeverity;
use App\Models\ISCPPEventLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * Discovers the available ISCPP event classes.
 *
 * Discovery is by reflection over app/Events, so adding an event is a single new file
 * with no registration step. This assumes one class per file, PSR-4, and that the class
 * name matches the file name.
 *
 * Registered as a singleton, so the scan happens once per process.
 */
class ISCPPEventRegistry
{
    private const NAMESPACE_PREFIX = 'App\\Events\\';

    /** @var Collection<string, class-string<ISCPPEventBase>>|null */
    private ?Collection $events = null;

    /**
     * All discovered event classes keyed by their machine key, ordered for the rule builder.
     *
     * @return Collection<string, class-string<ISCPPEventBase>>
     */
    public function all(): Collection
    {
        return $this->events ??= collect(glob(app_path('Events').DIRECTORY_SEPARATOR.'*.php'))
            ->map(fn (string $path) => self::NAMESPACE_PREFIX.pathinfo($path, PATHINFO_FILENAME))
            ->filter(fn (string $class) => class_exists($class)
                && is_subclass_of($class, ISCPPEventBase::class)
                && ! (new ReflectionClass($class))->isAbstract())
            ->mapWithKeys(fn (string $class) => [$class::key() => $class])
            ->sortKeys();
    }

    /**
     * @return array<string, string> key => label
     */
    public function options(): array
    {
        return $this->all()->map(fn (string $class) => $class::label())->all();
    }

    /**
     * Event keys grouped by their prefix, for a grouped rule builder.
     *
     * @return Collection<string, Collection<string, string>>
     */
    public function grouped(): Collection
    {
        return $this->all()
            ->map(fn (string $class) => $class::label())
            ->groupBy(fn (string $label, string $key) => Str::before($key, '.'));
    }

    public function labelFor(string $key): string
    {
        $class = $this->all()->get($key);

        return $class ? $class::label() : $key;
    }

    /**
     * @return array<string, string>
     */
    public function filterablePathsFor(string $key): array
    {
        $class = $this->all()->get($key);

        return $class ? $class::filterablePaths() : [];
    }

    public function keys(): array
    {
        return $this->all()->keys()->all();
    }

    /**
     * An example of the JSON a webhook receives for this event.
     *
     * Prefers a real recorded event so the example shows actual field values. Falls back
     * to a synthetic envelope when the event has never fired yet.
     *
     * @return array<string, mixed>
     */
    public function examplePayloadFor(string $key): array
    {
        $recorded = ISCPPEventLog::where('key', $key)->latest('id')->first();

        if ($recorded !== null) {
            return $recorded->payload ?? [];
        }

        $class = $this->all()->get($key);

        if ($class === null) {
            return [];
        }

        return [
            'iscpp' => [
                'payloadVersion' => 1,
                'instance' => config('app.url'),
            ],
            'event' => [
                'id' => '01920000-0000-7000-8000-000000000000',
                'key' => $key,
                'label' => $class::label(),
                'severity' => $class::defaultSeverity()->value,
                'occurredAt' => now()->toIso8601String(),
            ],
            'tenant' => [
                'id' => '00000000-0000-0000-0000-000000000000',
                'name' => 'Example Tenant GmbH',
                'showAs' => 'Example Tenant GmbH - 12345',
                'haloclientId' => 42,
                'ninjaorgId' => 7,
            ],
            'subject' => null,
            'data' => $class::exampleData(),
        ];
    }

    /**
     * Everything the event catalog shows about one event type, derived from the class.
     *
     * Properties come from exampleData(), so an event documents its own payload.
     *
     * @return array{
     *     key: string,
     *     label: string,
     *     class: string,
     *     description: string,
     *     severity: ISCPPEventSeverity,
     *     severityVaries: bool,
     *     properties: array<int, array{name: string, type: string, example: string}>,
     *     filterablePaths: array<string, string>,
     * }|null
     */
    public function describe(string $key): ?array
    {
        $class = $this->all()->get($key);

        if ($class === null) {
            return null;
        }

        return [
            'key' => $key,
            'label' => $class::label(),
            'class' => class_basename($class),
            'description' => $class::description(),
            'severity' => $class::defaultSeverity(),
            'severityVaries' => self::severityVaries($class),
            'properties' => collect($class::exampleData())
                ->map(fn (mixed $value, string $name) => [
                    'name' => $name,
                    'type' => get_debug_type($value),
                    'example' => is_scalar($value) ? var_export($value, true) : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ])
                ->values()
                ->all(),
            'filterablePaths' => $class::filterablePaths(),
        ];
    }

    /**
     * Whether an event decides its severity per occurrence rather than per type.
     *
     * True when the class overrides severity() instead of defaultSeverity().
     *
     * @param  class-string<ISCPPEventBase>  $class
     */
    public static function severityVaries(string $class): bool
    {
        return (new ReflectionMethod($class, 'severity'))->getDeclaringClass()->getName() !== ISCPPEventBase::class;
    }

    /**
     * Event keys arranged as a tree by their dot separated segments.
     *
     * "sc.alert.created" becomes sc > alert > created. A node is a leaf when it carries an
     * event key and a branch when it has children; a node can be both if one key is a
     * prefix of another.
     *
     * @return array<int, array{segment: string, label: string, key: ?string, severity: ?ISCPPEventSeverity, children: array<int, mixed>}>
     */
    public function tree(): array
    {
        $root = [];

        foreach ($this->all() as $key => $class) {
            $node = &$root;

            foreach (explode('.', $key) as $segment) {
                $node[$segment] ??= ['segment' => $segment, 'key' => null, 'children' => []];
                $leaf = &$node[$segment];
                $node = &$leaf['children'];
            }

            $leaf['key'] = $key;
            unset($node, $leaf);
        }

        return $this->labelTree($root);
    }

    /**
     * @param  array<string, array{segment: string, key: ?string, children: array<string, mixed>}>  $nodes
     * @return array<int, array{segment: string, path: string, label: string, key: ?string, severity: ?ISCPPEventSeverity, children: array<int, mixed>}>
     */
    private function labelTree(array $nodes, string $prefix = ''): array
    {
        return collect($nodes)
            ->map(function (array $node) use ($prefix) {
                $path = $prefix === '' ? $node['segment'] : $prefix.'.'.$node['segment'];

                return [
                    'segment' => $node['segment'],
                    'path' => $path,
                    // Short label: the parents already say "Sophos Central > Alert".
                    'label' => self::segmentLabel($node['segment']),
                    'key' => $node['key'],
                    'severity' => $node['key'] !== null ? $this->all()->get($node['key'])::defaultSeverity() : null,
                    'children' => $this->labelTree($node['children'], $path),
                ];
            })
            // Branches first, then leaves, each alphabetically.
            ->sortBy(fn (array $node) => [$node['children'] === [] ? 1 : 0, $node['label']])
            ->values()
            ->all();
    }

    /**
     * Human readable name for a key segment that groups events.
     */
    public static function segmentLabel(string $segment): string
    {
        return match ($segment) {
            'sc' => __('Sophos Central'),
            default => Str::headline($segment),
        };
    }
}
