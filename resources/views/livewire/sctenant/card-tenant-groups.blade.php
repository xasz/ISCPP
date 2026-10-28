<?php

use App\Models\SCTenant;
use Livewire\Volt\Component;

new class extends Component {
    public SCTenant $sctenant;

    public function mount(SCTenant $sctenant): void
    {
        $this->sctenant = $sctenant;
    }

    public function with(): array
    {
        return [
            'groups' => $this->sctenant->SCTenantGroups()->withCount('rules')->orderBy('name')->get(),
        ];
    }
}; ?>

<x-card title="{{ __('Tenant Groups') }}" subtitle="{{ __('Groups this tenant is a member of') }}">
    @if($groups->isEmpty())
        <flux:text size="sm" class="text-zinc-500">{{ __('This tenant is not a member of any group.') }}</flux:text>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach($groups as $group)
                <a href="{{ route('sctenantgroups.edit', $group) }}" wire:navigate title="{{ $group->description }}">
                    <flux:badge size="sm" icon="user-group">
                        {{ $group->name }}
                        @if($group->rules_count > 0)
                            · {{ $group->rules_count }} {{ __('rule(s)') }}
                        @endif
                    </flux:badge>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-2">
        <flux:link href="{{ route('sctenantgroups.index') }}" wire:navigate class="text-sm">{{ __('Manage groups') }}</flux:link>
    </div>
</x-card>
