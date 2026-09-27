@props(['title' => null])

@php
    $demos = [
        ['route' => 'demos.support', 'label' => 'Support Chat'],
        ['route' => 'demos.triage', 'label' => 'Email Triage'],
        ['route' => 'demos.debugger', 'label' => 'Log Debugger'],
        ['route' => 'demos.approvals', 'label' => 'Approval Desk'],
    ];

    $versions = [
        'promptphp/intercept' => \Composer\InstalledVersions::getPrettyVersion('promptphp/intercept'),
        'laravel/ai' => \Composer\InstalledVersions::getPrettyVersion('laravel/ai'),
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-canvas">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · Intercept demos' : 'Intercept demos' }}</title>
    <script>
        // Apply the theme before the first paint, so the page never flashes the wrong one.
        (() => {
            let theme = 'system';
            try { theme = localStorage.getItem('theme') ?? 'system'; } catch {}
            const dark = theme === 'dark' || (theme === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans text-fg-2 antialiased selection:bg-fg selection:text-canvas">
    <div class="lg:flex">
        <aside class="border-b border-line lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:shrink-0 lg:flex-col lg:border-r lg:border-b-0">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-5 pt-5 pb-3 text-fg lg:pt-7 lg:pb-6">
                <svg viewBox="0 0 20 20" fill="none" class="size-5" aria-hidden="true">
                    <path d="M10 2 3.5 4.5v5c0 4 2.8 7 6.5 8.5 3.7-1.5 6.5-4.5 6.5-8.5v-5L10 2Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                    <path d="M7 10h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span class="text-base font-semibold tracking-tight">Intercept</span>
                <span class="font-mono text-xs text-subtle">demos</span>
            </a>

            <nav class="flex gap-1 overflow-x-auto px-3 pb-3 lg:flex-col lg:pb-0">
                @foreach ($demos as $index => $demo)
                    <a href="{{ route($demo['route']) }}" @class([
                        'flex shrink-0 items-center gap-3 rounded-md px-2.5 py-2 text-sm transition',
                        'bg-tint-strong text-fg' => request()->routeIs($demo['route']),
                        'text-muted hover:bg-tint hover:text-fg-2' => ! request()->routeIs($demo['route']),
                    ])>
                        <span class="font-mono text-xs text-faint">0{{ $index + 1 }}</span>
                        {{ $demo['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="px-5 pb-3 lg:mt-auto lg:pb-0">
                <div role="radiogroup" aria-label="Theme" class="inline-flex rounded-lg bg-tint p-0.5 ring-1 ring-line ring-inset">
                    @foreach (['system' => 'System', 'light' => 'Light', 'dark' => 'Dark'] as $value => $label)
                        <button type="button" role="radio" aria-checked="false" data-theme-option="{{ $value }}"
                            class="rounded-md px-2.5 py-1 text-xs text-subtle transition hover:text-fg-2 aria-checked:bg-panel aria-checked:text-fg aria-checked:shadow-sm aria-checked:ring-1 aria-checked:ring-line">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <dl class="hidden px-5 pt-4 pb-6 font-mono text-xs leading-5 lg:block">
                @foreach ($versions as $package => $version)
                    <div class="flex flex-wrap justify-between gap-x-2">
                        <dt class="text-faint">{{ $package }}</dt>
                        <dd class="text-subtle">{{ $version }}</dd>
                    </div>
                @endforeach
            </dl>
        </aside>

        <main class="min-w-0 flex-1 px-5 py-8 lg:px-12 lg:py-12">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
