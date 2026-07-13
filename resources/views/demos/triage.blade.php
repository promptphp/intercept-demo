<x-layouts.demo title="Email Triage">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Demo 2 — Email Triage</h1>
                <p class="mt-1 text-sm text-zinc-400">Inbound emails are untrusted data. Injections get sanitized (not rejected) so every ticket is still triaged.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex flex-wrap gap-1.5">
                    <span class="rounded-md bg-amber-500/10 px-2 py-1 font-mono text-[11px] text-amber-400">PromptInjectionGuard(action: 'sanitize')</span>
                    <span class="rounded-md bg-amber-500/10 px-2 py-1 font-mono text-[11px] text-amber-400">PIIRedactor(blockEntities: [])</span>
                </div>
                <form method="POST" action="{{ route('demos.triage.reset') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-zinc-700 px-3 py-1.5 text-xs text-zinc-400 transition hover:border-zinc-500 hover:text-zinc-200">
                        Reset demo
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">
            <div class="flex flex-col gap-4">
                @foreach ($emails as $email)
                    <article data-email="{{ $email->id }}" class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-200">{{ $email->subject }}</p>
                                <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $email->from_name }} &lt;{{ $email->from_email }}&gt;</p>
                            </div>
                            <button type="button" data-triage-button data-url="{{ route('demos.triage.store', $email) }}"
                                class="shrink-0 rounded-lg bg-indigo-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50">
                                Triage
                            </button>
                        </div>

                        <p class="mt-3 line-clamp-3 text-xs leading-relaxed whitespace-pre-line text-zinc-400">{{ $email->body }}</p>

                        <div data-triage-result class="mt-4 flex flex-wrap items-center gap-2 {{ $email->triaged_at ? '' : 'hidden' }}">
                            <span data-category class="rounded-full bg-indigo-500/15 px-2.5 py-1 text-xs font-medium text-indigo-300">{{ $email->category }}</span>
                            <span data-priority class="rounded-full px-2.5 py-1 text-xs font-medium">{{ $email->priority }}</span>
                            <span data-summary class="w-full text-xs leading-relaxed text-zinc-400">{{ $email->summary }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-prompt-inspector class="sticky top-6" />
        </div>
    </div>

    <script type="module">
        const priorityStyles = {
            low: 'bg-zinc-700/40 text-zinc-300',
            normal: 'bg-sky-500/15 text-sky-300',
            high: 'bg-amber-500/15 text-amber-300',
            urgent: 'bg-red-500/15 text-red-300',
        };

        const applyPriorityStyle = (element) => {
            const priority = element.textContent.trim();
            element.className = `rounded-full px-2.5 py-1 text-xs font-medium ${priorityStyles[priority] ?? priorityStyles.normal}`;
        };

        document.querySelectorAll('[data-priority]').forEach(applyPriorityStyle);

        document.querySelectorAll('[data-triage-button]').forEach((button) => {
            button.addEventListener('click', async () => {
                const card = button.closest('[data-email]');
                button.disabled = true;
                button.textContent = 'Triaging…';

                try {
                    const { status, body } = await window.demo.post(button.dataset.url);

                    if (status !== 200) {
                        button.textContent = 'Failed — retry';
                        return;
                    }

                    const result = card.querySelector('[data-triage-result]');
                    result.querySelector('[data-category]').textContent = body.email.category;
                    const priority = result.querySelector('[data-priority]');
                    priority.textContent = body.email.priority;
                    applyPriorityStyle(priority);
                    result.querySelector('[data-summary]').textContent = body.email.summary;
                    result.classList.remove('hidden');

                    button.textContent = 'Re-triage';
                    window.demo.inspect({ original: body.originalPrompt, sent: body.sentPrompt });
                } catch (error) {
                    button.textContent = 'Failed — retry';
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
</x-layouts.demo>
