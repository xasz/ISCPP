@props(['label', 'value'])
<div class="flex flex-col sm:flex-row sm:items-center gap-1 py-2.5 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
    <dt class="text-xs font-medium uppercase tracking-wide text-neutral-400 dark:text-neutral-500 sm:w-44 shrink-0">{{ $label }}</dt>
    <dd class="text-sm text-neutral-800 dark:text-neutral-200 font-mono break-all">{{ $value }}</dd>
</div>