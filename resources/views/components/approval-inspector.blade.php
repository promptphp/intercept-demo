@php
    $panels = [
        [
            'title' => 'What the model proposed',
            'note' => 'Checked before it reaches you. By default only card numbers, API keys and bearer tokens count as a leak.',
            'hook' => 'data-proposal-findings',
            'tone' => 'text-muted',
        ],
        [
            'title' => 'What the support lead typed',
            'note' => 'Scanned before the tool runs. Intercept never rewrites a decision, so redact only logs.',
            'hook' => 'data-decision-segments',
            'tone' => 'text-good',
        ],
        [
            'title' => 'Tools that ran',
            'note' => 'Everything the SDK executed this turn. A blocked turn runs nothing.',
            'hook' => 'data-tools-run',
            'tone' => 'text-muted',
        ],
    ];
@endphp

<section data-approval-inspector {{ $attributes->class('rounded-xl bg-panel ring-1 ring-line') }}>
    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-3.5">
        <div>
            <h2 class="text-sm font-medium text-fg-2">Approval inspector</h2>
            <p class="text-xs text-subtle">Both ends of one pause</p>
        </div>
        <span data-approval-status class="rounded-full bg-tint px-2 py-0.5 text-xs font-medium text-muted ring-1 ring-edge ring-inset">waiting</span>
    </div>

    <ol class="flex flex-col gap-6 p-5">
        @foreach ($panels as $index => $panel)
            <li>
                <h3 class="flex items-baseline gap-2 text-sm font-medium text-fg-2">
                    <span class="font-mono text-xs text-faint">{{ $index + 1 }}</span>
                    {{ $panel['title'] }}
                </h3>
                <p class="mt-1 mb-2.5 text-xs leading-relaxed text-subtle">{{ $panel['note'] }}</p>
                <pre {{ $panel['hook'] }} @class(['max-h-56 overflow-auto rounded-lg bg-inset p-3.5 font-mono text-xs leading-relaxed whitespace-pre-wrap', $panel['tone']])>Nothing yet.</pre>
            </li>
        @endforeach
    </ol>
</section>
