<x-layouts.app>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-card-simple-info title="Firewalls" value="{{ $firewallsCount['all'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scfirewalls.index') }}">{{ __('Show all') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Connected" value="{{ $firewallsCount['connected'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scfirewalls.index', ['filterStatus' => 'connected']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Disconnected" value="{{ $firewallsCount['disconnected'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scfirewalls.index', ['filterStatus' => 'disconnected']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Suspended" value="{{ $firewallsCount['suspended'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scfirewalls.index', ['filterStatus' => 'suspended']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
    </div>

    <x-card title="Filter">
        <form action="{{ route('scfirewalls.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <x-card-details-input label="Hostname" name="filterHostname" value="{{ request('filterHostname') }}" />
            </div>
            <div class="w-44">
                <flux:select name="filterStatus" label="{{ __('Status') }}">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['connected', 'disconnected', 'suspended'] as $status)
                        <option value="{{ $status }}" @selected(request('filterStatus') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </flux:select>
            </div>
            <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
            @if(request()->hasAny(['filterHostname', 'filterStatus']))
                <flux:button href="{{ route('scfirewalls.index') }}" variant="ghost">{{ __('Reset') }}</flux:button>
            @endif
        </form>
    </x-card>

    <x-card title="Firewalls" subtitle="{{ $scfirewalls->total() }} {{ __('total') }}">
        <x-scfirewalls-table :scfirewalls="$scfirewalls" />
    </x-card>
</x-layouts.app>
