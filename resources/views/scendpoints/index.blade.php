<x-layouts.app>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-card-simple-info title="Endpoints" value="{{ $endpointsCount['all'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scendpoints.index') }}">{{ __('Show all') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Healthy" value="{{ $endpointsCount['good'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scendpoints.index', ['filterHealth' => 'good']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Suspicious" value="{{ $endpointsCount['suspicious'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scendpoints.index', ['filterHealth' => 'suspicious']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
        <x-card-simple-info title="Bad" value="{{ $endpointsCount['bad'] }}">
            <x-slot name="menu">
                <flux:menu.item href="{{ route('scendpoints.index', ['filterHealth' => 'bad']) }}">{{ __('Filter') }}</flux:menu.item>
            </x-slot>
        </x-card-simple-info>
    </div>

    <x-card title="Filter">
        <form action="{{ route('scendpoints.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div class="min-w-56 flex-1">
                <x-card-details-input label="Hostname" name="filterHostname" value="{{ request('filterHostname') }}" />
            </div>
            <div class="w-44">
                <flux:select name="filterHealth" label="{{ __('Health') }}">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['good', 'suspicious', 'bad', 'unknown'] as $health)
                        <option value="{{ $health }}" @selected(request('filterHealth') === $health)>{{ ucfirst($health) }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-44">
                <flux:select name="filterType" label="{{ __('Type') }}">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['computer', 'server'] as $type)
                        <option value="{{ $type }}" @selected(request('filterType') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </flux:select>
            </div>
            <flux:button type="submit" variant="primary">{{ __('Filter') }}</flux:button>
            @if(request()->hasAny(['filterHostname', 'filterHealth', 'filterType']))
                <flux:button href="{{ route('scendpoints.index') }}" variant="ghost">{{ __('Reset') }}</flux:button>
            @endif
        </form>
    </x-card>

    <x-card title="Endpoints" subtitle="{{ $scendpoints->total() }} {{ __('total') }}">
        <x-scendpoints-table :scendpoints="$scendpoints" />
    </x-card>
</x-layouts.app>
