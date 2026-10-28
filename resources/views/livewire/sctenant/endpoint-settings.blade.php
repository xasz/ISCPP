<?php

use App\Models\SCTenant;
use App\Services\SCService;
use Livewire\Volt\Component;

/**
 * Tenant wide endpoint settings, read live from Sophos when the tab is opened.
 */
new class extends Component {
    public SCTenant $sctenant;

    public bool $loaded = false;

    /** @var array<int, array<string, mixed>> */
    public array $policies = [];

    /** @var array<int, array<string, mixed>> */
    public array $blockedItems = [];

    /** @var array<int, array<string, mixed>> */
    public array $blockedAddresses = [];

    /** @var array<string, string> */
    public array $failures = [];

    public function mount(SCTenant $sctenant): void
    {
        $this->sctenant = $sctenant;
    }

    public function load(SCService $service): void
    {
        $this->failures = [];

        $this->policies = collect($this->attempt('policies', fn () => $service->endpointPolicies($this->sctenant)->all()) ?? [])
            ->map(fn (array $policy) => [
                'name' => $policy['name'] ?? '',
                'type' => $policy['type'] ?? '',
                'enabled' => (bool) ($policy['enabled'] ?? false),
                'priority' => $policy['priority'] ?? null,
                'endpoints' => count(data_get($policy, 'appliesTo.endpoints', [])),
                'endpointGroups' => count(data_get($policy, 'appliesTo.endpointGroups', [])),
                'users' => count(data_get($policy, 'appliesTo.users', [])) + count(data_get($policy, 'appliesTo.userGroups', [])),
                'lockedByManagingAccount' => (bool) ($policy['lockedByManagingAccount'] ?? false),
            ])
            ->sortBy([['type', 'asc'], ['priority', 'asc']])
            ->values()
            ->all();

        $this->blockedItems = $this->attempt('blockedItems', fn () => $service->blockedItems($this->sctenant)->all()) ?? [];
        $this->blockedAddresses = $this->attempt('blockedAddresses', fn () => $service->blockedAddresses($this->sctenant)->all()) ?? [];

        $this->loaded = true;
    }

    private function attempt(string $section, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $exception) {
            $this->failures[$section] = $exception->getMessage();

            return null;
        }
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
        <x-card title="{{ __('Policies') }}" subtitle="{{ count($policies) }} {{ __('total') }}">
            @isset($failures['policies'])
                <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load policies.')" :text="$failures['policies']" />
            @else
                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact>{{ __('Name') }}</x-table.th>
                                <x-table.th compact>{{ __('Type') }}</x-table.th>
                                <x-table.th compact class="w-20">{{ __('Priority') }}</x-table.th>
                                <x-table.th compact class="w-24">{{ __('Enabled') }}</x-table.th>
                                <x-table.th compact>{{ __('Applies to') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($policies as $policy)
                                <x-table.tr>
                                    <x-table.td compact class="font-medium">
                                        {{ $policy['name'] }}
                                        @if($policy['lockedByManagingAccount'])
                                            <flux:badge size="sm" color="zinc" icon="lock-closed">{{ __('Managed') }}</flux:badge>
                                        @endif
                                    </x-table.td>
                                    <x-table.td compact class="text-xs">{{ $policy['type'] }}</x-table.td>
                                    <x-table.td compact>{{ $policy['priority'] }}</x-table.td>
                                    <x-table.td compact><flux:badge size="sm" :color="$policy['enabled'] ? 'green' : 'zinc'">{{ $policy['enabled'] ? __('Yes') : __('No') }}</flux:badge></x-table.td>
                                    <x-table.td compact class="text-xs">
                                        @if($policy['endpoints'] + $policy['endpointGroups'] + $policy['users'] === 0)
                                            {{ __('No assignment') }}
                                        @else
                                            {{ $policy['endpoints'] }} {{ __('endpoints') }},
                                            {{ $policy['endpointGroups'] }} {{ __('endpoint groups') }},
                                            {{ $policy['users'] }} {{ __('users / user groups') }}
                                        @endif
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">{{ __('No policies found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </x-table.table>
                </div>
            @endisset
        </x-card>

        <x-card title="{{ __('Blocked Items') }}" subtitle="{{ count($blockedItems) }} {{ __('total') }}">
            @isset($failures['blockedItems'])
                <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load blocked items.')" :text="$failures['blockedItems']" />
            @else
                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact>{{ __('File') }}</x-table.th>
                                <x-table.th compact>{{ __('SHA256') }}</x-table.th>
                                <x-table.th compact>{{ __('Comment') }}</x-table.th>
                                <x-table.th compact>{{ __('Created') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($blockedItems as $item)
                                <x-table.tr>
                                    <x-table.td compact>
                                        {{ data_get($item, 'properties.fileName', __('N/A')) }}
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">{{ data_get($item, 'properties.path') }}</div>
                                    </x-table.td>
                                    <x-table.td compact class="font-mono text-xs" title="{{ data_get($item, 'properties.sha256') }}">{{ Str::limit((string) data_get($item, 'properties.sha256'), 16) }}</x-table.td>
                                    <x-table.td compact class="whitespace-normal text-xs">{{ data_get($item, 'comment') }}</x-table.td>
                                    <x-table.td compact class="text-xs">
                                        {{ data_get($item, 'createdAt') }}
                                        <div class="text-zinc-400 dark:text-zinc-500">{{ data_get($item, 'createdBy.name') }}</div>
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">{{ __('No blocked items.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </x-table.table>
                </div>
            @endisset
        </x-card>

        <x-card title="{{ __('Blocked Addresses') }}" subtitle="{{ count($blockedAddresses) }} {{ __('total') }}">
            @isset($failures['blockedAddresses'])
                <flux:callout variant="danger" icon="x-circle" :heading="__('Could not load blocked addresses.')" :text="$failures['blockedAddresses']" />
            @else
                <div class="relative overflow-x-auto">
                    <x-table.table>
                        <x-table.thead>
                            <tr>
                                <x-table.th compact>{{ __('Address') }}</x-table.th>
                                <x-table.th compact>{{ __('Comment') }}</x-table.th>
                                <x-table.th compact>{{ __('Blocked until') }}</x-table.th>
                                <x-table.th compact>{{ __('Created') }}</x-table.th>
                            </tr>
                        </x-table.thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($blockedAddresses as $address)
                                <x-table.tr>
                                    <x-table.td compact class="font-mono text-xs">{{ data_get($address, 'item') }}</x-table.td>
                                    <x-table.td compact class="whitespace-normal text-xs">{{ data_get($address, 'comment') }}</x-table.td>
                                    <x-table.td compact class="text-xs">{{ data_get($address, 'blockedUntil', __('Unlimited')) }}</x-table.td>
                                    <x-table.td compact class="text-xs">
                                        {{ data_get($address, 'createdAt') }}
                                        <div class="text-zinc-400 dark:text-zinc-500">{{ data_get($address, 'createdBy.name') }}</div>
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">{{ __('No blocked addresses.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </x-table.table>
                </div>
            @endisset
        </x-card>
    @endunless
</div>
