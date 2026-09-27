<x-layouts.demo title="Email Triage">
    <div class="mx-auto max-w-6xl">
        <x-demo-header number="2" title="Email Triage">
            Inbound email is untrusted. Injections are stripped out, not rejected, so every ticket still gets triaged.

            <x-slot:aside>
                <x-policy>PromptInjectionGuard(action: 'sanitize')</x-policy>
                <x-policy>PIIRedactor(blockEntities: [])</x-policy>
                <form method="POST" action="{{ route('demos.triage.reset') }}">
                    @csrf
                    <button type="submit" class="px-1 text-xs text-subtle transition hover:text-fg-2">Reset</button>
                </form>
            </x-slot:aside>
        </x-demo-header>

        <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-2">
            <div class="flex flex-col gap-3">
                @foreach ($emails as $email)
                    <article data-email="{{ $email->id }}" class="rounded-xl bg-panel p-5 ring-1 ring-line">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-fg">{{ $email->subject }}</p>
                                <p class="mt-0.5 truncate text-xs text-subtle">{{ $email->from_name }} &lt;{{ $email->from_email }}&gt;</p>
                            </div>
                            <button type="button" data-triage-button data-url="{{ route('demos.triage.store', $email) }}"
                                class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-tint-strong px-3 py-1.5 text-xs font-medium text-fg transition hover:bg-tint-hover disabled:cursor-not-allowed disabled:opacity-40">
                                Triage
                            </button>
                        </div>

                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed whitespace-pre-line text-muted">{{ $email->body }}</p>

                        <div data-triage-result @class(['mt-4 flex flex-wrap items-center gap-1.5 border-t border-line pt-3', 'hidden' => ! $email->triaged_at])>
                            <span data-category class="rounded-full bg-tint px-2 py-0.5 text-xs font-medium text-fg-2 ring-1 ring-edge ring-inset">{{ $email->category }}</span>
                            <span data-priority class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset">{{ $email->priority }}</span>
                            <p data-summary class="mt-1 w-full text-xs leading-relaxed text-muted">{{ $email->summary }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-prompt-inspector class="xl:sticky xl:top-8" />
        </div>
    </div>

    <script type="module">
        const priorityStyles = {
            low: 'bg-tint text-muted ring-edge',
            normal: 'bg-sky-500/10 text-info ring-sky-500/20',
            high: 'bg-amber-500/10 text-warn ring-amber-500/20',
            urgent: 'bg-red-500/10 text-bad ring-red-500/20',
        };

        const applyPriorityStyle = (element) => {
            const priority = element.textContent.trim();
            element.className = `rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${priorityStyles[priority] ?? priorityStyles.normal}`;
        };

        document.querySelectorAll('[data-priority]').forEach(applyPriorityStyle);

        document.querySelectorAll('[data-triage-button]').forEach((button) => {
            button.addEventListener('click', async () => {
                const card = button.closest('[data-email]');
                button.disabled = true;
                window.demo.loading(button, 'Triaging');

                try {
                    const { status, body } = await window.demo.post(button.dataset.url);

                    if (status !== 200) {
                        button.textContent = 'Retry';
                        return;
                    }

                    const result = card.querySelector('[data-triage-result]');
                    result.querySelector('[data-category]').textContent = body.email.category;
                    const priority = result.querySelector('[data-priority]');
                    priority.textContent = body.email.priority;
                    applyPriorityStyle(priority);
                    result.querySelector('[data-summary]').textContent = body.email.summary;
                    result.classList.remove('hidden');

                    button.textContent = 'Triage again';
                    window.demo.inspect({ original: body.originalPrompt, sent: body.sentPrompt });
                } catch (error) {
                    button.textContent = 'Retry';
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
</x-layouts.demo>
