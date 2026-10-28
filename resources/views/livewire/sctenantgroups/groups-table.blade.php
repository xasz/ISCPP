<?php

use App\Models\SCTenantGroup;
use Livewire\Volt\Component;

new class extends Component {
    public string $message = '';

    public function with(): array
    {
        return [
            'groups' => SCTenantGroup::query()
                ->withCount(['tenants', 'rules'])
                ->orderBy('name')
                ->get(),
        ];
    }

    public function delete(int $id): void
    {
        $group = SCTenantGroup::withCount('rules')->findOrFail($id);

        // The rules foreign key restricts this too. Checking first gives a readable message
        // instead of a database error.
        if ($group->rules_count > 0) {
            $this->message = __('This group is used by :count rule(s). Change those rules first.', ['count' => $group->rules_count]);

            return;
        }

        $group->delete();
        $this->message = __('Group deleted');
    }
}; ?>

<x-card title="{{ __('Tenant Groups') }}" subtitle="{{ __('Manually maintained sets of tenants, for example to scope rules') }}">
    <div class="flex items-center justify-end">
        <flux:button variant="primary" icon="plus" size="sm" href="{{ route('sctenantgroups.create') }}" wire:navigate>
            {{ __('Add Group') }}
        </flux:button>
    </div>

    <div class="relative overflow-x-auto">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th compact>{{ __('Name') }}</x-table.th>
                    <x-table.th compact>{{ __('Description') }}</x-table.th>
                    <x-table.th compact class="w-24">{{ __('Tenants') }}</x-table.th>
                    <x-table.th compact class="w-24">{{ __('Rules') }}</x-table.th>
                    <x-table.th compact class="w-36"></x-table.th>
                </tr>
            </x-table.thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($groups as $group)
                    <x-table.tr>
                        <x-table.td compact class="font-medium">{{ $group->name }}</x-table.td>
                        <x-table.td compact class="whitespace-normal text-xs text-zinc-500 dark:text-zinc-400">{{ $group->description }}</x-table.td>
                        <x-table.td compact>{{ $group->tenants_count }}</x-table.td>
                        <x-table.td compact>{{ $group->rules_count }}</x-table.td>
                        <x-table.td compact>
                            <div class="flex items-center gap-2">
                                <flux:button size="xs" href="{{ route('sctenantgroups.edit', $group) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                                <flux:button size="xs" variant="danger" wire:click="delete({{ $group->id }})" wire:confirm="{{ __('Delete this group?') }}">{{ __('Delete') }}</flux:button>
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
                            {{ __('No tenant groups yet.') }}
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
