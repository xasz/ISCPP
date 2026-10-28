<?php

use App\Models\Event;
use App\Models\SCEndpoint;
use App\Services\SCService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

/**
 * Live data of one endpoint, read from Sophos when the tab is opened and never stored.
 */
new class extends Component {
    public SCEndpoint $endpoint;

    public bool $loaded = false;

    /** @var array<string, mixed> */
    public array $details = [];

    /** @var array<string, mixed> */
    public array $isolation = [];

    /**
     * Tamper protection without any password. The current password is only fetched on
     * an explicit reveal, so it never sits in the page state otherwise.
     *
     * @var array{enabled?: bool, previousPasswordChanges?: array<int, string>}
     */
    public array $tamperProtection = [];

    public ?string $revealedPassword = null;

    /** @var array<int, array<string, mixed>> */
    public array $policies = [];

    /** @var array<string, string> */
    public array $failures = [];

    public function mount(SCEndpoint $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function load(SCService $service): void
    {
        $this->failures = [];
        $this->revealedPassword = null;
        $tenant = $this->endpoint->SCTenant;

        if ($tenant === null) {
            $this->failures['tenant'] = __('The tenant of this endpoint is unknown.');
            $this->loaded = true;

            return;
        }

        $this->details = $this->attempt('details', fn () => $service->endpoint($tenant, $this->endpoint->id, 'full')) ?? [];
        $this->isolation = $this->attempt('isolation', fn () => $service->endpointIsolation($tenant, $this->endpoint->id)) ?? [];

        $tamper = $this->attempt('tamperProtection', fn () => $service->endpointTamperProtection($tenant, $this->endpoint->id));
        $this->tamperProtection = $tamper === null ? [] : [
            'enabled' => (bool) ($tamper['enabled'] ?? false),
            'previousPasswordChanges' => collect($tamper['previousPasswords'] ?? [])->pluck('invalidatedAt')->filter()->values()->all(),
        ];

        $policies = $this->attempt('policies', fn () => $service->endpointPolicies($tenant)->all());
        $this->policies = $policies === null ? [] : $this->policiesForEndpoint($policies);

        $this->loaded = true;
    }

    public function revealPassword(SCService $service): void
    {
        $tenant = $this->endpoint->SCTenant;

        if ($tenant === null) {
            return;
        }

        $tamper = $this->attempt('tamperProtection', fn () => $service->endpointTamperProtection($tenant, $this->endpoint->id));

        if ($tamper === null) {
            return;
        }

        $this->revealedPassword = (string) ($tamper['password'] ?? '');

        Event::logInfo('scendpoints', 'Tamper protection password revealed', [
            'endpoint' => $this->endpoint->id,
            'SCTenant' => $tenant->id,
            'user' => Auth::user()?->email,
        ]);
    }

    public function hidePassword(): void
    {
        $this->revealedPassword = null;
    }

    /**
     * Run one API call, recording its failure instead of failing the whole tab.
     */
    private function attempt(string $section, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            $this->failures[$section] = $exception->getMessage();

            return null;
        }
    }

    /**
     * Policies are tenant wide. Only those that name this endpoint or its endpoint group
     * are directly attributable; policies without an assignment are listed as well,
     * since that is where the base policy lives. User based assignments cannot be
     * resolved to an endpoint and are left out.
     *
     * @param  array<int, array<string, mixed>>  $policies
     * @return array<int, array<string, mixed>>
     */
    private function policiesForEndpoint(array $policies): array
    {
        $groupId = data_get($this->details, 'group.id') ?? $this->endpoint->groupId();
        $isServer = $this->endpoint->type === 'server';

        return collect($policies)
            ->filter(fn (array $policy) => str_starts_with((string) ($policy['type'] ?? ''), 'server-') === $isServer)
            ->map(function (array $policy) use ($groupId) {
                $appliesTo = $policy['appliesTo'] ?? [];

                $assignment = match (true) {
                    in_array($this->endpoint->id, $appliesTo['endpoints'] ?? [], true) => 'endpoint',
                    $groupId !== null && in_array($groupId, $appliesTo['endpointGroups'] ?? [], true) => 'group',
                    $appliesTo === [] => 'unassigned',
                    default => null,
                };

                return $assignment === null ? null : [
                    'name' => $policy['name'] ?? '',
                    'type' => $policy['type'] ?? '',
                    'enabled' => (bool) ($policy['enabled'] ?? false),
                    'priority' => $policy['priority'] ?? null,
                    'assignment' => $assignment,
                ];
            })
            ->filter()
            ->sortBy([['type', 'asc'], ['priority', 'asc']])
            ->values()
            ->all();
    }
}; ?>

<div class="flex flex-col gap-4" wire:init="load">
    <div class="flex items-center justify-between">
        <flux:text size="sm" class="text-zinc-500">{{ __('Read live from Sophos Central when this tab is opened. Nothing here is stored.') }}</flux:text>
        <flux:button size="sm" icon="arrow-path" wire:click="load">{{ __('Reload') }}</flux:button>
    </div>

    @unless($loaded)
        <x-card>
            <div class="flex items-center gap-2">
                <flux:icon.loading class="size-4" />
                <flux:text>{{ __('Loading live data from Sophos Central…') }}</flux:text>
            </div>
        </x-card>
    @else
        @isset($failures['tenant'])
            <flux:callout variant="danger" icon="x-circle" :heading="$failures['tenant']" />
        @endisset

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card title="{{ __('Status') }}">
                @isset($failures['details'])
                    <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load the endpoint.')" :text="$failures['details']" />
                @else
                    <div class="flex flex-wrap gap-2">
                        <flux:badge :color="App\Models\SCEndpoint::healthColor(data_get($details, 'health.overall'))" size="sm">{{ __('Health') }}: {{ data_get($details, 'health.overall', __('unknown')) }}</flux:badge>
                        <flux:badge :color="data_get($details, 'online') ? 'green' : 'zinc'" size="sm">{{ data_get($details, 'online') ? __('Online') : __('Offline') }}</flux:badge>
                        @if(data_get($details, 'cloned'))
                            <flux:badge color="amber" size="sm">{{ __('Cloned') }}</flux:badge>
                        @endif
                    </div>
                    <div class="grid gap-x-6 sm:grid-cols-2">
                        <x-card-details-row label="{{ __('Last Seen') }}" :value="data_get($details, 'lastSeenAt', __('N/A'))" />
                        <x-card-details-row label="{{ __('Last Agent Update') }}" :value="data_get($details, 'lastAgentUpdateAt', __('N/A'))" />
                        <x-card-details-row label="{{ __('Last OS Update') }}" :value="data_get($details, 'lastOsUpdateAt', __('N/A'))" />
                        <x-card-details-row label="{{ __('Lockdown') }}" :value="data_get($details, 'lockdown.status', __('N/A'))" />
                        <x-card-details-row label="{{ __('Encryption') }}" :value="data_get($details, 'encryption.overallStatus', __('N/A'))" />
                        <x-card-details-row label="{{ __('Serial Number') }}" :value="data_get($details, 'serialNumber', __('N/A'))" />
                    </div>
                    @if(count(data_get($details, 'tags', [])) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach(data_get($details, 'tags', []) as $tag)
                                <flux:badge size="sm" color="zinc">{{ data_get($tag, 'displayString') ?? data_get($tag, 'key') }}</flux:badge>
                            @endforeach
                        </div>
                    @endif
                @endisset
            </x-card>

            <x-card title="{{ __('Isolation') }}">
                @isset($failures['isolation'])
                    <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load the isolation state.')" :text="$failures['isolation']" />
                @else
                    <flux:badge :color="data_get($isolation, 'enabled') ? 'red' : 'green'" size="sm">
                        {{ data_get($isolation, 'enabled') ? __('Isolated') : __('Not isolated') }}
                    </flux:badge>
                    <div class="grid gap-x-6 sm:grid-cols-2">
                        <x-card-details-row label="{{ __('Last Isolated') }}" :value="data_get($isolation, 'lastEnabledAt', __('Never'))" />
                        <x-card-details-row label="{{ __('Isolated By') }}" :value="data_get($isolation, 'lastEnabledBy.name') ?? data_get($isolation, 'lastEnabledBy.id', __('N/A'))" />
                        <x-card-details-row label="{{ __('Last Released') }}" :value="data_get($isolation, 'lastDisabledAt', __('Never'))" />
                        <x-card-details-row label="{{ __('Released By') }}" :value="data_get($isolation, 'lastDisabledBy.name') ?? data_get($isolation, 'lastDisabledBy.id', __('N/A'))" />
                    </div>
                    @if(filled(data_get($isolation, 'comment')))
                        <x-card-details-row label="{{ __('Comment') }}" :value="data_get($isolation, 'comment')" />
                    @endif
                @endisset
            </x-card>

            <x-card title="{{ __('Tamper Protection') }}">
                @isset($failures['tamperProtection'])
                    <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load tamper protection.')" :text="$failures['tamperProtection']" />
                @else
                    <flux:badge :color="data_get($tamperProtection, 'enabled') ? 'green' : 'red'" size="sm">
                        {{ data_get($tamperProtection, 'enabled') ? __('Enabled') : __('Disabled') }}
                    </flux:badge>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:text size="sm" class="font-medium">{{ __('Password') }}:</flux:text>
                        @if($revealedPassword !== null)
                            <code class="rounded bg-zinc-100 px-2 py-0.5 font-mono text-sm dark:bg-zinc-800">{{ $revealedPassword }}</code>
                            <flux:button size="xs" variant="ghost" icon="eye-slash" wire:click="hidePassword">{{ __('Hide') }}</flux:button>
                        @else
                            <code class="rounded bg-zinc-100 px-2 py-0.5 font-mono text-sm dark:bg-zinc-800">••••••••</code>
                            <flux:button size="xs" icon="eye" wire:click="revealPassword">{{ __('Show') }}</flux:button>
                        @endif
                    </div>
                    <flux:text size="sm" class="text-zinc-500">{{ __('Showing the password is recorded in the system log.') }}</flux:text>
                    @if(count(data_get($tamperProtection, 'previousPasswordChanges', [])) > 0)
                        <x-card-details-row label="{{ __('Previous passwords invalidated at') }}" :value="collect(data_get($tamperProtection, 'previousPasswordChanges'))->implode(', ')" />
                    @endif
                @endisset
            </x-card>

            <x-card title="{{ __('Services') }}">
                @forelse(data_get($details, 'health.services.serviceDetails', []) as $service)
                    <div class="flex items-center justify-between gap-4">
                        <flux:text size="sm" class="truncate">{{ data_get($service, 'name') }}</flux:text>
                        <flux:badge :color="data_get($service, 'status') === 'running' ? 'green' : 'red'" size="sm">{{ data_get($service, 'status') }}</flux:badge>
                    </div>
                @empty
                    <flux:text size="sm" class="text-zinc-500">{{ __('No service details reported.') }}</flux:text>
                @endforelse
            </x-card>
        </div>

        <x-card title="{{ __('Policies') }}" subtitle="{{ __('Policies assigned to this endpoint or its endpoint group, plus policies without an assignment such as the base policy. User based assignments are not shown.') }}">
            @isset($failures['policies'])
                <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load policies.')" :text="$failures['policies']" />
            @else
                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact>{{ __('Name') }}</x-table.th>
                                <x-table.th compact>{{ __('Type') }}</x-table.th>
                                <x-table.th compact class="w-24">{{ __('Priority') }}</x-table.th>
                                <x-table.th compact class="w-28">{{ __('Enabled') }}</x-table.th>
                                <x-table.th compact class="w-36">{{ __('Assigned via') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($policies as $policy)
                                <x-table.tr>
                                    <x-table.td compact class="font-medium">{{ $policy['name'] }}</x-table.td>
                                    <x-table.td compact class="text-xs">{{ $policy['type'] }}</x-table.td>
                                    <x-table.td compact>{{ $policy['priority'] }}</x-table.td>
                                    <x-table.td compact><flux:badge size="sm" :color="$policy['enabled'] ? 'green' : 'zinc'">{{ $policy['enabled'] ? __('Yes') : __('No') }}</flux:badge></x-table.td>
                                    <x-table.td compact class="text-xs">
                                        {{ match ($policy['assignment']) { 'endpoint' => __('Endpoint'), 'group' => __('Endpoint group'), default => __('No assignment') } }}
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">{{ __('No matching policies.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-table.table>
                </div>
                @if($endpoint->SCTenant)
                    <flux:link href="{{ route('sctenants.tenantEndpointSettings', $endpoint->SCTenant) }}" wire:navigate class="text-sm">
                        {{ __('All policies, blocked items and blocked addresses of this tenant') }}
                    </flux:link>
                @endif
            @endisset
        </x-card>

        @if($details !== [])
            <x-card title="{{ __('Full live response') }}">
                <x-card-details-json :arr="$details" />
            </x-card>
        @endif
    @endunless
</div>
