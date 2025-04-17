
<div class="my-4 p-4 relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
    <section>
            <header>
                @if(isset($title))
                <flux:heading size="xl" level="2">{{ $title }}</flux:heading>    
                @endif
                @if(isset($subtitle))
                <flux:text size="lg" class="mt-1 mb-6">{{ $subtitle }}</flux:text> 
                @endif
            </header>
    {{$slot}}
    </section>
</div>
