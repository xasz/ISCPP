<x-layouts.app>
    @php

    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-card-simple-info title="Tenants" value="{{ $tenantsCount }}" />
            <x-card-simple-info title="Jobs in Queue" value="{{ $jobsInQueue }}" />
            <x-card-simple-info title="Alerts last 24h" value="{{ $alerts24HCount }}" />
        </div>
        <div class="relative h-full flex-1 rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
