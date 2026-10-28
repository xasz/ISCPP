<?php

use App\Models\ISCPPEventRule;
use App\Models\SCTenant;
use App\Models\SCTenantGroup;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public ?int $groupId = null;

    public string $name = '';

    public string $description = '';

    /** @var array<int, string> */
    public array $tenantIds = [];

    public string $search = '';

    public function mount(?int $groupId = null): void
    {
        $this->groupId = $groupId;

        if ($groupId === null) {
            return;
        }

        $group = SCTenantGroup::findOrFail($groupId);

        $this->name = $group->name;
        $this->description = (string) $group->description;
        $this->tenantIds = $group->tenants()->pluck('sc_tenants.id')->all();
    }

    public function with(): array
    {
        return [
            'sctenants' => SCTenant::orderBy('name')
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($query) {
                        $query->whereLike('name', '%'.$this->search.'%')
                            ->orWhereLike('showAs', '%'.$this->search.'%');
                    });
                })
                ->get(['id', 'name', 'showAs']),
        ];
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sc_tenant_groups', 'name')->ignore($this->groupId)],
            'description' => 'nullable|string|max:1000',
            'tenantIds' => 'array',
            'tenantIds.*' => 'string|exists:sc_tenants,id',
        ]);

        $group = $this->groupId ? SCTenantGroup::findOrFail($this->groupId) : new SCTenantGroup;

        $group->fill([
            'name' => $this->name,
            'description' => $this->description ?: null,
        ])->save();

        $group->tenants()->sync($this->tenantIds);

        // Rules matching this group are cached with their members.
        ISCPPEventRule::flushCache();

        return $this->redirect(route('sctenantgroups.index'), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-card :title="$groupId ? __('Edit Tenant Group') : __('Add Tenant Group')">
        <x-card-details-input label="{{ __('Name') }}" wire:model="name" />
        @error('name') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

        <x-card-details-input label="{{ __('Description (optional)') }}" wire:model="description" />
        @error('description') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
    </x-card>

    <x-card title="{{ __('Tenants') }}" subtitle="{{ count($tenantIds) }} {{ __('selected') }}">
        <div class="w-full max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search tenants') }}" size="sm" />
        </div>

        <div class="mt-2 grid gap-x-6 gap-y-1 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($sctenants as $sctenant)
                <div class="min-w-0" wire:key="tenant-{{ $sctenant->id }}">
                    <flux:checkbox wire:model.live="tenantIds" value="{{ $sctenant->id }}" label="{{ $sctenant->name }}" title="{{ $sctenant->name }} ({{ $sctenant->showAs }})" />
                </div>
            @empty
                <flux:text size="sm" class="text-zinc-500">{{ __('No tenants found.') }}</flux:text>
            @endforelse
        </div>
        @error('tenantIds.*') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
    </x-card>

    <x-card>
        <div class="flex items-center justify-end gap-2">
            <flux:button variant="ghost" href="{{ route('sctenantgroups.index') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            <x-a-button wire:click="save">{{ $groupId ? __('Save Group') : __('Create Group') }}</x-a-button>
        </div>
    </x-card>
</div>
