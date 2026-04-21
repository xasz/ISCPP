@props(['json' =>'', 'arr' => null])
@php
    if(is_array($json)) {
        $arr = $json;
    }
    
    if($arr) {
        $json = json_encode($arr);
    }
    $pretty = collect(json_decode($json, true))->toJson(JSON_PRETTY_PRINT);
@endphp
<div class="mt-4">
    <div class="flex items-center justify-between mb-1.5">
        <span class="text-xs font-medium uppercase tracking-wide text-neutral-400 dark:text-neutral-500">{{ __('Raw Data') }}</span>
        <button
            onclick="navigator.clipboard.writeText(this.closest('div').nextElementSibling.querySelector('pre').textContent)"
            class="text-xs text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition"
            title="{{ __('Copy') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-4 12h6a2 2 0 002-2v-8a2 2 0 00-2-2h-6a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
            {{ __('Copy') }}
        </button>
    </div>
    <div class="overflow-auto rounded-lg bg-neutral-950 dark:bg-black border border-neutral-800">
        <pre class="p-4 text-xs leading-relaxed text-emerald-400 font-mono">{{ $pretty }}</pre>
    </div>
</div>
