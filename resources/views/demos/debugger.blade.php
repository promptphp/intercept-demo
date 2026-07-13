<x-layouts.demo title="Log Debugger">
    <div class="mx-auto max-w-6xl">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Demo 3 — Log Debugger</h1>
                <p class="mt-1 text-sm text-zinc-400">Paste a production log. PII is masked so you can still correlate values — leaked secrets block the request entirely.</p>
            </div>
            <div class="flex flex-wrap gap-1.5">
                <span class="rounded-md bg-amber-500/10 px-2 py-1 font-mono text-[11px] text-amber-400">PIIRedactor(action: 'mask')</span>
                <span class="rounded-md bg-red-500/10 px-2 py-1 font-mono text-[11px] text-red-400">blockEntities: ['api_key', 'bearer_token']</span>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-2">
            <div class="flex flex-col gap-6">
                <section class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                    <div class="mb-3 flex flex-wrap gap-1.5">
                        <button type="button" data-load-sample="pii" class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400 transition hover:border-indigo-500/60 hover:text-zinc-200">
                            Sample: query bug with customer PII
                        </button>
                        <button type="button" data-load-sample="token" class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400 transition hover:border-indigo-500/60 hover:text-zinc-200">
                            Sample: 401 with leaked bearer token
                        </button>
                        <button type="button" data-load-sample="clean" class="rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400 transition hover:border-indigo-500/60 hover:text-zinc-200">
                            Sample: clean stack trace
                        </button>
                    </div>
                    <form data-log-form class="flex flex-col gap-3">
                        <textarea name="log" required maxlength="20000" rows="12" spellcheck="false"
                            placeholder="Paste a log excerpt or stack trace…"
                            class="w-full resize-y rounded-lg border border-zinc-700 bg-zinc-950 p-4 font-mono text-xs leading-relaxed placeholder-zinc-600 focus:border-indigo-500 focus:outline-none"></textarea>
                        <button type="submit" data-analyze
                            class="self-end rounded-lg bg-indigo-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50">
                            Analyze log
                        </button>
                    </form>
                </section>

                <section data-analysis-panel class="hidden rounded-xl border border-zinc-800 bg-zinc-900/50">
                    <header class="border-b border-zinc-800 px-5 py-4">
                        <h2 class="text-sm font-semibold tracking-wide text-zinc-200 uppercase">Analysis</h2>
                    </header>
                    <pre data-analysis class="max-h-96 overflow-auto p-5 text-sm leading-relaxed whitespace-pre-wrap text-zinc-300"></pre>
                </section>
            </div>

            <x-prompt-inspector class="sticky top-6" />
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

        document.querySelectorAll('[data-load-sample]').forEach((button) => {
            button.addEventListener('click', () => {
                textarea.value = samples[button.dataset.loadSample];
                textarea.focus();
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const log = textarea.value.trim();
            if (!log) return;

            analyzeButton.disabled = true;
            analyzeButton.textContent = 'Analyzing…';
            analysisPanel.classList.add('hidden');

            try {
                const { status, body } = await window.demo.post('{{ route('demos.debugger.store') }}', { log });

                if (status === 422 && body.blocked) {
                    analysis.textContent = `🛡️ ${body.reason}`;
                    analysisPanel.classList.remove('hidden');
                    window.demo.inspect({ original: log, blocked: true });
                } else if (status === 200) {
                    analysis.textContent = body.reply;
                    analysisPanel.classList.remove('hidden');
                    window.demo.inspect({ original: log, sent: body.sentPrompt });
                } else {
                    analysis.textContent = `Something went wrong (${status}).`;
                    analysisPanel.classList.remove('hidden');
                }
            } catch (error) {
                analysis.textContent = 'Request failed. Is your AI provider key configured?';
                analysisPanel.classList.remove('hidden');
            } finally {
                analyzeButton.disabled = false;
                analyzeButton.textContent = 'Analyze log';
            }
        });
    </script>
</x-layouts.demo>
