@props(['samples'])

<div {{ $attributes->class('flex flex-wrap items-center gap-1.5') }}>
    <span class="mr-1 text-xs text-subtle">Try</span>
    @foreach ($samples as $sample)
        <button type="button" data-sample
            class="rounded-full bg-tint px-3 py-1 text-left text-xs text-muted ring-1 ring-edge ring-inset transition hover:bg-tint-strong hover:text-fg-2">{{ $sample }}</button>
    @endforeach
</div>
