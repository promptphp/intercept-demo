<section data-inspector {{ $attributes->class('rounded-xl bg-panel ring-1 ring-line') }}>
    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-3.5">
        <div>
            <h2 class="text-sm font-medium text-fg-2">Prompt inspector</h2>
            <p class="text-xs text-subtle">What actually left your server</p>
        </div>
        <span data-inspector-status class="rounded-full bg-tint px-2 py-0.5 text-xs font-medium text-muted ring-1 ring-edge ring-inset">waiting</span>
    </div>

    <div class="flex flex-col gap-5 p-5">
        <div>
            <h3 class="mb-2 text-xs font-medium text-subtle">You typed</h3>
            <pre data-inspector-original class="max-h-60 overflow-auto rounded-lg bg-inset p-3.5 font-mono text-xs leading-relaxed whitespace-pre-wrap text-muted">Nothing yet.</pre>
        </div>
        <div>
            <h3 class="mb-2 text-xs font-medium text-subtle">Sent to provider</h3>
            <pre data-inspector-sent class="max-h-60 overflow-auto rounded-lg bg-inset p-3.5 font-mono text-xs leading-relaxed whitespace-pre-wrap text-good">Nothing yet.</pre>
        </div>
    </div>
</section>
