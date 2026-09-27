@props(['title' => null])

<section {{ $attributes->class('rounded-xl bg-panel ring-1 ring-line') }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-3.5">
            <h2 class="text-sm font-medium text-fg-2">{{ $title }}</h2>
            {{ $actions ?? '' }}
        </div>
    @endif

    {{ $slot }}
</section>
