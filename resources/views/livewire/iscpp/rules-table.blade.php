<?php

use App\Models\ISCPPEventRule;
use Livewire\Volt\Component;

new class extends Component {
    public array $rules = [];

    public string $message = '';

    public function mount(): void
    {
        $this->refreshRules();
    }

    public function refreshRules(): void
    {
        $this->rules = ISCPPEventRule::query()
            ->with('SCTenant')
            ->withCount('conditions')
            ->orderBy('name')
            ->get()
            ->map(fn (ISCPPEventRule $rule) => [
                'id' => $rule->id,
                'name' => $rule->name,
                'enabled' => $rule->enabled,
                'eventLabel' => $rule->eventLabel(),
                'eventKey' => $rule->event_key,
                'tenant' => $rule->SCTenant?->name,
                'url' => $rule->url,
                'conditionsCount' => $rule->conditions_count,
                'autoDisabled' => $rule->wasAutoDisabled(),
                'failures' => $rule->consecutive_failures,
                'failedCount' => $rule->deliveries()->where('status', 'failed')->count(),
            ])->all();
    }

    public function toggle(int $id): void
    {
        $rule = ISCPPEventRule::findOrFail($id);

        if ($rule->enabled) {
            $rule->update(['enabled' => false]);
            $this->message = __('Rule disabled');
        } else {
            // Enabling by hand also clears the circuit breaker.
            $rule->reenable();
            $this->message = __('Rule enabled');
        }

        ISCPPEventRule::flushCache();
        $this->refreshRules();
    }

    public function delete(int $id): void
    {
        ISCPPEventRule::findOrFail($id)->delete();

        ISCPPEventRule::flushCache();
        $this->refreshRules();
        $this->message = __('Rule deleted');
    }
}; ?>

<x-card title="{{ __('ISCPP Event Rules') }}" subtitle="{{ __('Each rule forwards one event to one webhook') }}">
    <div class="flex items-center justify-end">
        <flux:button variant="primary" icon="plus" size="sm" href="{{ route('iscpp.rules.create') }}" wire:navigate>
            {{ __('Add Rule') }}
        </flux:button>
    </div>

    <div class="relative overflow-x-auto">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th compact>{{ __('Name') }}</x-table.th>
                    <x-table.th compact>{{ __('Event') }}</x-table.th>
                    <x-table.th compact>{{ __('Tenant') }}</x-table.th>
                    <x-table.th compact class="w-24">{{ __('Conditions') }}</x-table.th>
                    <x-table.th compact>{{ __('Delivers to') }}</x-table.th>
                    <x-table.th compact class="w-36">{{ __('Status') }}</x-table.th>
                    <x-table.th compact class="w-44"></x-table.th>
                </tr>
            </x-table.thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($rules as $rule)
                    <x-table.tr>
                        <x-table.td compact class="font-medium">{{ $rule['name'] }}</x-table.td>
                        <x-table.td compact class="text-xs">
                            {{ $rule['eventLabel'] }}
                            <div class="text-zinc-400 dark:text-zinc-500">{{ $rule['eventKey'] }}</div>
                        </x-table.td>
                        <x-table.td compact class="text-xs">{{ $rule['tenant'] ?? __('All tenants') }}</x-table.td>
                        <x-table.td compact>{{ $rule['conditionsCount'] }}</x-table.td>
                        <x-table.td compact class="break-all text-xs">{{ $rule['url'] }}</x-table.td>
                        <x-table.td compact>
                            @if($rule['enabled'])
                                <flux:badge color="green" size="sm">{{ __('Enabled') }}</flux:badge>
                            @elseif($rule['autoDisabled'])
                                <flux:badge color="red" size="sm">{{ __('Auto-disabled') }}</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">{{ __('Disabled') }}</flux:badge>
                            @endif
                            @if($rule['failures'] > 0)
                                <flux:badge color="amber" size="sm">{{ $rule['failures'] }} {{ __('fails') }}</flux:badge>
                            @endif
                            @if($rule['failedCount'] > 0)
                                <flux:badge color="red" size="sm">{{ $rule['failedCount'] }} {{ __('failed') }}</flux:badge>
                            @endif
                        </x-table.td>
                        <x-table.td compact>
                            <div class="flex items-center gap-2">
                                <flux:button size="xs" href="{{ route('iscpp.rules.edit', $rule['id']) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click="toggle({{ $rule['id'] }})">
                                    {{ $rule['enabled'] ? __('Disable') : __('Enable') }}
                                </flux:button>
                                <flux:button size="xs" variant="danger" wire:click="delete({{ $rule['id'] }})" wire:confirm="{{ __('Delete this rule?') }}">{{ __('Delete') }}</flux:button>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
                            {{ __('No rules yet. Events are still recorded in the event log, they are just not forwarded anywhere.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table.table>
    </div>

    @if($message !== '')
        <div class="py-2">{{ $message }}</div>
    @endif
</x-card>
