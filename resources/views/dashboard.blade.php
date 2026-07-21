<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-card-simple-info title="Tenants" value="{{ $tenantsCount }}" />
            <x-card-simple-info title="Jobs in Queue" value="{{ $jobsInQueue }}" />
            <x-card-simple-info title="Alerts last 24h" value="{{ $alerts24HCount }}" />
        </div>

        @if($awareness->isNotEmpty())
            <x-card title="Awareness" subtitle="Here you see some hints you should be aware of">
                <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($awareness as $aware)
                        <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <flux:badge color="amber" size="sm">!</flux:badge>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $aware['message'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    </div>
</x-layouts.app>
