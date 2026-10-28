<?php

use App\Jobs\CreateSCFirewallBackup;
use App\Models\SCFirewall;
use App\Models\SCFirewallBackup;
use App\Settings\SCServiceSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public SCFirewall $firewall;

    public string $message = '';

    public function mount(SCFirewall $firewall): void
    {
        $this->firewall = $firewall;
    }

    public function with(): array
    {
        $settings = resolve(SCServiceSettings::class);

        return [
            'backups' => $this->firewall->backups()->orderByDesc('created_at')->orderByDesc('id')->get(),
            'scheduleEnabled' => (bool) $settings->firewallBackupsScheduleEnabled,
            'retention' => (int) $settings->firewallBackupsRetention,
        ];
    }

    public function createBackup(): void
    {
        if ($this->firewall->backups()->where('status', SCFirewallBackup::STATUS_PENDING)->exists()) {
            $this->message = __('A backup of this firewall is already running.');

            return;
        }

        if (! $this->firewall->isConnected()) {
            $this->message = __('The firewall is not connected to Sophos Central, so it cannot export its configuration.');

            return;
        }

        CreateSCFirewallBackup::dispatch($this->firewall->id, (string) (Auth::user()?->email ?? 'manual'));

        $this->message = __('Backup queued. The export usually takes a few minutes.');
    }

    public function deleteBackup(int $id): void
    {
        $this->firewall->backups()->findOrFail($id)->deleteWithFile();

        $this->message = __('Backup deleted.');
    }
}; ?>

<x-card title="{{ __('Configuration Backups') }}" subtitle="{{ __('Full configuration exports of this firewall') }}">
    <div wire:poll.15s class="flex flex-wrap items-center justify-between gap-3">
        <flux:text size="sm" class="text-zinc-500">
            @if($scheduleEnabled)
                {{ __('A backup is created automatically every night. The newest :count finished backups are kept.', ['count' => $retention]) }}
            @else
                {{ __('Automatic backups are disabled in the Sophos Central feature settings. The newest :count finished backups are kept.', ['count' => $retention]) }}
            @endif
        </flux:text>
        <flux:button size="sm" variant="primary" icon="arrow-down-tray" wire:click="createBackup" wire:confirm="{{ __('Export the configuration of this firewall now?') }}">
            {{ __('Create backup now') }}
        </flux:button>
    </div>

    @if($message !== '')
        <flux:text size="sm">{{ $message }}</flux:text>
    @endif

    <div class="relative overflow-x-auto">
        <x-table.table>
            <x-table.thead>
                <tr>
                    <x-table.th compact class="w-44">{{ __('Created') }}</x-table.th>
                    <x-table.th compact class="w-28">{{ __('Status') }}</x-table.th>
                    <x-table.th compact class="w-28">{{ __('Size') }}</x-table.th>
                    <x-table.th compact>{{ __('Requested by') }}</x-table.th>
                    <x-table.th compact>{{ __('Details') }}</x-table.th>
                    <x-table.th compact class="w-48"></x-table.th>
                </tr>
            </x-table.thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse($backups as $backup)
                    <x-table.tr wire:key="backup-{{ $backup->id }}">
                        <x-table.td compact class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">{{ App\Services\ISCPPFormat::formatDateWithSeconds($backup->created_at) }}</x-table.td>
                        <x-table.td compact><flux:badge size="sm" :color="$backup->statusColor()">{{ $backup->status }}</flux:badge></x-table.td>
                        <x-table.td compact>{{ $backup->file_size !== null ? Illuminate\Support\Number::fileSize($backup->file_size) : '' }}</x-table.td>
                        <x-table.td compact class="text-xs">{{ $backup->requested_by }}</x-table.td>
                        <x-table.td compact class="whitespace-normal text-xs">
                            @if($backup->error)
                                <span class="text-red-600 dark:text-red-400">{{ Str::limit($backup->error, 200) }}</span>
                            @elseif($backup->sha256)
                                <span class="font-mono" title="SHA256 {{ $backup->sha256 }}">{{ Str::limit($backup->sha256, 16) }}</span>
                            @endif
                        </x-table.td>
                        <x-table.td compact>
                            <div class="flex items-center gap-2">
                                @if($backup->isDownloadable())
                                    <flux:button size="xs" icon="arrow-down-tray" href="{{ route('scfirewalls.backupDownload', ['id' => $firewall->id, 'backup' => $backup->id]) }}">{{ __('Download') }}</flux:button>
                                @endif
                                @if($backup->status !== App\Models\SCFirewallBackup::STATUS_PENDING)
                                    <flux:button size="xs" variant="danger" wire:click="deleteBackup({{ $backup->id }})" wire:confirm="{{ __('Delete this backup?') }}">{{ __('Delete') }}</flux:button>
                                @endif
                            </div>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">{{ __('No backups yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </x-table.table>
    </div>
</x-card>
