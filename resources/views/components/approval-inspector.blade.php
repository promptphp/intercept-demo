<section data-approval-inspector {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-zinc-800 bg-zinc-900/50']) }}>
    <header class="flex items-center justify-between gap-4 border-b border-zinc-800 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold tracking-wide text-zinc-200 uppercase">Approval inspector</h2>
            <p class="text-xs text-zinc-500">Both ends of one pause</p>
        </div>
        <span data-approval-status class="rounded-full bg-zinc-800 px-2.5 py-1 text-xs font-medium text-zinc-400">waiting</span>
    </header>
    <div class="grid grid-cols-1 divide-y divide-zinc-800">
        <div class="p-5">
            <h3 class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-wide text-zinc-500 uppercase">
                <span class="rounded bg-indigo-500/15 px-1.5 py-0.5 font-mono text-[10px] text-indigo-400">1</span>
                What the model proposed
            </h3>
            <p class="mb-2 text-xs leading-relaxed text-zinc-600">
                <span class="font-mono text-zinc-500">ToolApprovalGuard</span> inspects the proposed call before
                it is surfaced for review. It acts on the response, because this content came from the model —
                shaped by tool results the pipeline never sees. It scans for three things by default:
                <span class="font-mono text-zinc-500">credit_card</span>,
                <span class="font-mono text-zinc-500">api_key</span>,
                <span class="font-mono text-zinc-500">bearer_token</span>. An email address in
                <span class="font-mono text-zinc-500">to:</span> is the tool's parameter, not a leak.
            </p>
            <pre data-proposal-findings class="max-h-56 overflow-auto rounded-lg bg-zinc-950/80 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-zinc-400">—</pre>
        </div>
        <div class="p-5">
            <h3 class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-wide text-zinc-500 uppercase">
                <span class="rounded bg-indigo-500/15 px-1.5 py-0.5 font-mono text-[10px] text-indigo-400">2</span>
                What the support lead typed
            </h3>
            <p class="mb-2 text-xs leading-relaxed text-zinc-600">
                A resumed run carries no prompt text. These segments are the only new content, and a resumed
                prompt cannot be rewritten — so <span class="font-mono text-zinc-500">redact</span> has nowhere
                to write and degrades to logging.
            </p>
            <pre data-decision-segments class="max-h-56 overflow-auto rounded-lg bg-zinc-950/80 p-4 font-mono text-xs leading-relaxed whitespace-pre-wrap text-emerald-300/90">—</pre>
        </div>
    </div>
</section>
