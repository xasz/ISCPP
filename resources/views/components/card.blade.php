
<div class="my-4 p-2 relativeflex flex-col">
    <div class="ml-2 mb-1">
        @if(isset($title))
        <flux:heading size="lg" level="2">{{ $title }}</flux:heading>    
        @endif
        @if(isset($subtitle))
        <flux:text size="lg" class="mt-1 mb-2">{{ $subtitle }}</flux:text> 
        @endif
    </div>
    <div class="p-2 rounded border border-zinc-300 dark:border-zinc-700">
        {{$slot}}
    </div>
</div>
