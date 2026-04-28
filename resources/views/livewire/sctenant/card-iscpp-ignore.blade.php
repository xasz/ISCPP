<?php

use App\Models\SCTenant;
use Livewire\Volt\Component;

new class extends Component {
    public bool $iscppIgnore = false;

    public string $message = '';

    public SCTenant $sctenant;

    public function mount(SCTenant $sctenant): void
    {
        $this->sctenant = $sctenant;
        $this->iscppIgnore = $sctenant->iscpp_ignore;
    }

    public function save(): void
    {
        $this->sctenant->update([
            'iscpp_ignore' => $this->iscppIgnore,
        ]);

        $this->message = __('Saved');
    }
}; ?>

<x-card>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('ISCPP Ignore') }}
            </h2>
        </header>

        <div class="mt-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('Exclude Tenant from ISCPP Queries') }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('When enabled, this tenant will be ignored in ISCPP queries and queue dispatches.') }}</p>
            </div>

            <flux:switch wire:model="iscppIgnore" />
        </div>

        <div class="mt-4 grid justify-items-end">
            <x-a-button wire:click="save">{{ __('Save') }}</x-a-button>
        </div>

        @if($message !== '')
            <div class="py-2">
                {{ $message }}
            </div>
        @endif
    </section>
</x-card>
