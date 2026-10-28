<?php

use App\Jobs\RefreshSCEndpoint;
use App\Models\SCEndpoint;
use Livewire\Volt\Component;

new class extends Component {
    public SCEndpoint $endpoint;

    public string $message = '';

    public function mount(SCEndpoint $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function refresh(): void
    {
        if ($this->endpoint->SCTenant?->iscpp_ignore) {
            $this->message = __('The tenant of this endpoint is ignored by ISCPP and is not refreshed.');

            return;
        }

        RefreshSCEndpoint::dispatch($this->endpoint->id);

        $this->message = __('Endpoint refresh queued.');
    }
}; ?>

<div class="flex flex-col items-end gap-1">
    <flux:button size="sm" icon="arrow-path" wire:click="refresh">{{ __('Refresh') }}</flux:button>
    @if($message !== '')
        <flux:text size="sm" class="text-zinc-500">{{ $message }}</flux:text>
    @endif
</div>
