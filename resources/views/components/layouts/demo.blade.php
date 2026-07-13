@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — Intercept Demos' : 'Intercept Demos' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 font-sans text-zinc-100 antialiased">
    <div class="flex min-h-screen">
        <aside class="flex w-72 shrink-0 flex-col gap-10 border-r border-zinc-800 bg-zinc-900/50 p-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/15 text-xl">🛡️</span>
                <span>
                    <span class="block text-lg font-semibold tracking-tight">Intercept</span>
                    <span class="block text-xs text-zinc-500">Laravel AI SDK middleware</span>
                </span>
            </a>

            <nav class="flex flex-col gap-1">
                @foreach ([
                    ['route' => 'demos.support', 'label' => 'Support Chat', 'number' => '1', 'hint' => 'Block injections & PII'],
                    ['route' => 'demos.triage', 'label' => 'Email Triage', 'number' => '2', 'hint' => 'Sanitize untrusted emails'],
                    ['route' => 'demos.debugger', 'label' => 'Log Debugger', 'number' => '3', 'hint' => 'Keep secrets on your server'],
                ] as $item)
                    <a href="{{ route($item['route']) }}"
                        class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ request()->routeIs($item['route']) ? 'bg-indigo-500/15 text-indigo-300' : 'text-zinc-400 hover:bg-zinc-800/70 hover:text-zinc-200' }}">
                        <span class="flex size-7 shrink-0 items-center justify-center rounded-md text-xs font-semibold {{ request()->routeIs($item['route']) ? 'bg-indigo-500/20 text-indigo-300' : 'bg-zinc-800 text-zinc-500 group-hover:text-zinc-300' }}">
                            {{ $item['number'] }}
                        </span>
                        <span>
                            <span class="block text-sm font-medium">Demo {{ $item['number'] }} — {{ $item['label'] }}</span>
                            <span class="block text-xs text-zinc-500">{{ $item['hint'] }}</span>
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto rounded-lg border border-zinc-800 bg-zinc-900 p-4 text-xs leading-relaxed text-zinc-500">
                Every prompt passes through <span class="text-zinc-300">promptphp/intercept</span> middleware before it reaches the AI provider.
            </div>
        </aside>

        <main class="min-w-0 flex-1 p-6 lg:p-10">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
