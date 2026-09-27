@props(['number', 'title'])

<header {{ $attributes->class('mb-8 flex flex-wrap items-end justify-between gap-x-10 gap-y-4') }}>
    <div class="max-w-2xl">
        <p class="font-mono text-xs text-subtle">Demo {{ $number }}</p>
        <h1 class="mt-1.5 text-[1.75rem] leading-tight font-semibold tracking-tight text-fg">{{ $title }}</h1>
        <p class="mt-2 text-base leading-relaxed text-muted">{{ $slot }}</p>
    </div>

    @isset($aside)
        <div class="flex flex-wrap items-center gap-2">{{ $aside }}</div>
    @endisset
</header>
