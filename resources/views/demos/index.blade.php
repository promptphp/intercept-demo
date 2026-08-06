<x-layouts.demo>
    <div class="mx-auto max-w-4xl">
        <div class="mb-12">
            <p class="mb-3 text-sm font-medium text-indigo-400">promptphp/intercept + Laravel AI SDK</p>
            <h1 class="text-4xl font-bold tracking-tight">Guardrails for your AI agents</h1>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-zinc-400">
                Four demo apps, four agents, four different middleware policies — built from three Intercept
                middleware: <span class="text-zinc-200">PromptInjectionGuard</span>,
                <span class="text-zinc-200">PIIRedactor</span>, and
                <span class="text-zinc-200">ToolApprovalGuard</span>.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                [
                    'route' => 'demos.support',
                    'emoji' => '💬',
                    'title' => 'Support Chat',
                    'description' => 'A storefront chatbot that refuses to be jailbroken and never forwards credit cards.',
                    'chips' => ['InjectionGuard: block', 'PIIRedactor: redact'],
                ],
                [
                    'route' => 'demos.triage',
                    'emoji' => '📥',
                    'title' => 'Email Triage',
                    'description' => 'Untrusted inbound emails are sanitized, not rejected — every ticket still gets triaged.',
                    'chips' => ['InjectionGuard: sanitize', 'PIIRedactor: redact'],
                ],
                [
                    'route' => 'demos.debugger',
                    'emoji' => '🐛',
                    'title' => 'Log Debugger',
                    'description' => 'Paste production logs safely: PII is masked and leaked secrets block the request.',
                    'chips' => ['PIIRedactor: mask + block secrets'],
                ],
                [
                    'route' => 'demos.approvals',
                    'emoji' => '✋',
                    'title' => 'Approval Desk',
                    'description' => 'An agent pauses for human approval. Both the call it proposed and the human\'s reply get scanned.',
                    'chips' => ['ToolApprovalGuard: block', 'scanApprovalDecisions'],
                ],
            ] as $index => $card)
                <a href="{{ route($card['route']) }}"
                    class="group flex flex-col gap-4 rounded-xl border border-zinc-800 bg-zinc-900/50 p-6 transition hover:border-indigo-500/50 hover:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <span class="text-3xl">{{ $card['emoji'] }}</span>
                        <span class="text-xs font-semibold text-zinc-600 group-hover:text-indigo-400">Demo {{ $index + 1 }}</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold">{{ $card['title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-400">{{ $card['description'] }}</p>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-1.5">
                        @foreach ($card['chips'] as $chip)
                            <span class="rounded-md bg-zinc-800 px-2 py-1 font-mono text-[11px] text-zinc-400">{{ $chip }}</span>
                        @endforeach
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts.demo>
