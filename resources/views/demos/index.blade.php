<x-layouts.demo>
    <div class="mx-auto max-w-4xl">
        <div class="mb-10 max-w-2xl">
            <p class="font-mono text-xs text-subtle">promptphp/intercept + laravel/ai</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-fg sm:text-4xl">Guardrails for Laravel AI agents</h1>
            <p class="mt-3 text-base leading-relaxed text-muted">
                Four small apps, each running its own Intercept policy. Try to make them leak.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @foreach ([
                [
                    'route' => 'demos.support',
                    'title' => 'Support Chat',
                    'description' => 'A storefront assistant that will not be jailbroken and never forwards a card number.',
                    'policies' => ['InjectionGuard: block', 'PIIRedactor: redact'],
                ],
                [
                    'route' => 'demos.triage',
                    'title' => 'Email Triage',
                    'description' => 'Untrusted inbound email is sanitized, not rejected, so every ticket still gets triaged.',
                    'policies' => ['InjectionGuard: sanitize', 'PIIRedactor: redact'],
                ],
                [
                    'route' => 'demos.debugger',
                    'title' => 'Log Debugger',
                    'description' => 'Paste production logs safely. PII is masked and leaked secrets stop the request.',
                    'policies' => ['PIIRedactor: mask', 'block secrets'],
                ],
                [
                    'route' => 'demos.approvals',
                    'title' => 'Approval Desk',
                    'description' => 'An agent pauses for sign-off. Intercept checks the call it proposes and your reply.',
                    'policies' => ['ToolApprovalGuard: block', 'scanApprovalDecisions'],
                ],
            ] as $index => $card)
                <a href="{{ route($card['route']) }}"
                    class="group flex flex-col gap-5 rounded-xl bg-panel p-5 ring-1 ring-line transition hover:bg-panel-hover hover:ring-edge-strong">
                    <div>
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 class="font-medium text-fg">{{ $card['title'] }}</h2>
                            <span class="font-mono text-xs text-faint transition group-hover:text-muted">0{{ $index + 1 }}</span>
                        </div>
                        <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $card['description'] }}</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        @foreach ($card['policies'] as $policy)
                            <x-policy>{{ $policy }}</x-policy>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.demo>
