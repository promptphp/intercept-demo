<section data-inspector {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50']) }}>
    <header class="flex items-center justify-between gap-4 border-b border-zinc-800 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold tracking-wide text-zinc-200 uppercase">Prompt inspector</h2>
            <p class="text-xs text-zinc-500">What actually left your server</p>
        </div>
        <span data-inspector-status class="rounded-full bg-zinc-800 px-2.5 py-1 text-xs font-medium text-zinc-400">waiting</span>
    </header>
    <div class="grid grid-cols-1 divide-y divide-zinc-800">
        <div class="p-5">
            <h3 class="mb-2 text-xs font-semibold tracking-wide text-zinc-500 uppercase">User input</h3>
            <pre data-inspector-original class="max-h-56 overflow-auto rounded-lg bg-zinc-950/80 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-zinc-400">—</pre>
        </div>
        <div class="p-5">
            <h3 class="mb-2 text-xs font-semibold tracking-wide text-zinc-500 uppercase">Sent to provider</h3>
            <pre data-inspector-sent class="max-h-56 overflow-auto rounded-lg bg-zinc-950/80 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-emerald-300/90">—</pre>
        </div>
    </div>
</section>
