@props(['defaultTab' => 'first'])

<div
    x-data="{
        activeTab: '{{ $defaultTab }}',
        init() {
            const hash = window.location.hash.replace('#tab-', '');
            if (hash) this.activeTab = hash;
            this.$watch('activeTab', tab => {
                history.replaceState(null, '', '#tab-' + tab);
            });
        }
    }"
    {{ $attributes->class(['']) }}
>
    <div class="border-b border-zinc-200 dark:border-zinc-800 mb-6">
        <nav class="-mb-px flex gap-1 overflow-x-auto">
            {{ $tabs }}
        </nav>
    </div>

    <div>
        {{ $content }}
    </div>
</div>
