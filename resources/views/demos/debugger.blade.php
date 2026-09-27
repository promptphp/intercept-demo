<x-layouts.demo title="Log Debugger">
    <div class="mx-auto max-w-6xl">
        <x-demo-header number="3" title="Log Debugger">
            Paste a production log. PII is masked so you can still correlate values. A leaked secret stops the request.

            <x-slot:aside>
                <x-policy>PIIRedactor(action: 'mask')</x-policy>
                <x-policy>blockEntities: ['api_key', 'bearer_token']</x-policy>
            </x-slot:aside>
        </x-demo-header>

        <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-2">
            <div class="flex flex-col gap-5">
                <x-panel title="Log">
                    <form data-log-form class="flex flex-col gap-3 p-4">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="mr-1 text-xs text-subtle">Load</span>
                            @foreach (['pii' => 'Query bug with customer PII', 'token' => '401 with a leaked bearer token', 'clean' => 'Clean stack trace'] as $key => $label)
                                <button type="button" data-load-sample="{{ $key }}"
                                    class="rounded-full bg-tint px-3 py-1 text-xs text-muted ring-1 ring-edge ring-inset transition hover:bg-tint-strong hover:text-fg-2">{{ $label }}</button>
                            @endforeach
                        </div>

                        <textarea name="log" required maxlength="20000" rows="12" spellcheck="false"
                            placeholder="Paste a log excerpt or stack trace"
                            class="w-full resize-y rounded-lg bg-field p-3.5 font-mono text-xs leading-relaxed text-fg-2 ring-1 ring-edge ring-inset placeholder:text-faint focus:ring-2 focus:ring-focus focus:outline-none"></textarea>

                        <button type="submit" data-analyze disabled
                            class="inline-flex items-center gap-2 self-end rounded-lg bg-accent px-4 py-2.5 text-sm font-medium text-on-accent transition hover:bg-accent-hover disabled:cursor-not-allowed disabled:opacity-40">
                            Analyze
                        </button>
                    </form>
                </x-panel>

                <x-panel title="Analysis" data-analysis-panel class="hidden">
                    <pre data-analysis class="max-h-96 overflow-auto p-5 text-sm leading-relaxed whitespace-pre-wrap text-fg-2"></pre>
                </x-panel>
            </div>

            <x-prompt-inspector class="xl:sticky xl:top-8" />
        </div>
    </div>

    <script type="module">
        const samples = {
            pii: `[2026-07-11 09:14:22] production.ERROR: SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: users.email (SQL: insert into "users" ("name", "email", "created_at") values (Emily Carter, emily.carter@gmail.com, 2026-07-11 09:14:22))
{"exception":"[object] (Illuminate\\\\Database\\\\QueryException(code: 23000))"}
#0 /var/www/app/Services/ImportCustomers.php(112): Illuminate\\Database\\Connection->run()
#1 /var/www/app/Console/Commands/SyncCrmCustomers.php(48): App\\Services\\ImportCustomers->handle()
Request context: {"ip":"203.0.113.42","user_agent":"Mozilla/5.0"}`,
            token: `[2026-07-11 10:03:51] production.ERROR: GuzzleHttp\\Exception\\ClientException: Client error: \`POST https://api.payments.example/v1/charges\` resulted in a \`401 Unauthorized\` response
Request headers: {"Authorization":"Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJiaWxsaW5nLXNlcnZpY2UifQ.k7pDMx9WvR4tYq2LsG8uZbNcE1fJhAoP"}
#0 /var/www/app/Services/PaymentGateway.php(87): GuzzleHttp\\Client->request()
#1 /var/www/app/Jobs/CaptureCharge.php(35): App\\Services\\PaymentGateway->charge()`,
            clean: `[2026-07-11 12:40:18] local.ERROR: Call to undefined method App\\Models\\Order::scopeShipped()
{"exception":"[object] (BadMethodCallException(code: 0))"}
#0 /var/www/app/Http/Controllers/OrderController.php(31): Illuminate\\Database\\Eloquent\\Builder->__call()
#1 /var/www/vendor/laravel/framework/src/Illuminate/Routing/Controller.php(54): App\\Http\\Controllers\\OrderController->index()`,
        };

        const form = document.querySelector('[data-log-form]');
        const textarea = form.querySelector('textarea[name="log"]');
        const analyzeButton = form.querySelector('[data-analyze]');
        const analysisPanel = document.querySelector('[data-analysis-panel]');
        const analysis = document.querySelector('[data-analysis]');
        const submit = window.demo.requireInput(textarea, analyzeButton);

        const show = (text, blocked = false) => {
            analysis.textContent = text;
            analysis.classList.toggle('text-bad-soft', blocked);
            analysis.classList.toggle('text-fg-2', !blocked);
            analysisPanel.classList.remove('hidden');
        };

        document.querySelectorAll('[data-load-sample]').forEach((button) => {
            button.addEventListener('click', () => {
                textarea.value = samples[button.dataset.loadSample];
                submit.sync();
                textarea.focus();
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const log = textarea.value.trim();
            if (!log) return;

            submit.busy(true);
            const stopLoading = window.demo.loading(analyzeButton, 'Analyzing');
            analysisPanel.classList.add('hidden');

            try {
                const { status, body } = await window.demo.post('{{ route('demos.debugger.store') }}', { log });

                if (status === 422 && body.blocked) {
                    show(`Blocked. ${body.reason}`, true);
                    window.demo.inspect({ original: log, blocked: true });
                } else if (status === 200) {
                    show(body.reply);
                    window.demo.inspect({ original: log, sent: body.sentPrompt });
                } else {
                    show(`Something went wrong (${status}).`, true);
                }
            } catch (error) {
                show('Request failed. Is your AI provider key set?', true);
            } finally {
                stopLoading('Analyze');
                submit.busy(false);
            }
        });
    </script>
</x-layouts.demo>
