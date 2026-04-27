<div class="rounded border border-zinc-200/80 bg-white shadow-sm ring-1 ring-zinc-950/5 transition-shadow hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:ring-white/10 overflow-hidden">
    @if(isset($title) || isset($subtitle))
    <div class="border-b border-blue-100 bg-gradient-to-r from-blue-50 to-blue-100/70 px-6 py-1 dark:border-blue-900/60 dark:from-blue-950/40 dark:to-blue-900/30">
        @if(isset($title))
        <flux:heading size="lg" level="2" class="leading-tight text-blue-900 dark:text-blue-100">{{ $title }}</flux:heading>
        @endif
        @if(isset($subtitle))
        <flux:text size="sm" class="mt-1 block text-blue-700/90 dark:text-blue-300/85">{{ $subtitle }}</flux:text>
        @endif
    </div>
    @endif

    <div class="px-6 py-6">
        {{$slot}}
    </div>
</div>
