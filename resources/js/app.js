const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const pillTones = {
    neutral: 'bg-tint text-muted ring-edge',
    good: 'bg-emerald-500/10 text-good ring-emerald-500/20',
    warn: 'bg-amber-500/10 text-warn ring-amber-500/20',
    bad: 'bg-red-500/10 text-bad ring-red-500/20',
};

const dots = `<span class="inline-flex items-center gap-1" aria-hidden="true">
    <span class="size-1.5 animate-bounce rounded-full bg-muted"></span>
    <span class="size-1.5 animate-bounce rounded-full bg-muted [animation-delay:150ms]"></span>
    <span class="size-1.5 animate-bounce rounded-full bg-muted [animation-delay:300ms]"></span>
</span>`;

const spinner = `<svg class="size-3.5 animate-spin" viewBox="0 0 16 16" fill="none" aria-hidden="true">
    <circle cx="8" cy="8" r="6" stroke="currentColor" stroke-opacity="0.25" stroke-width="2"/>
    <path d="M14 8a6 6 0 0 0-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
</svg>`;

/** Restyle a status pill and set its label. */
const setPill = (element, tone, label) => {
    element.className = `rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ${pillTones[tone]}`;
    element.textContent = label;
};

const themeQuery = window.matchMedia('(prefers-color-scheme: dark)');

const storedTheme = () => {
    try {
        return localStorage.getItem('theme') ?? 'system';
    } catch {
        return 'system';
    }
};

/** Apply a theme choice: 'system', 'light' or 'dark'. */
const applyTheme = (theme) => {
    const dark = theme === 'dark' || (theme === 'system' && themeQuery.matches);

    document.documentElement.classList.toggle('dark', dark);
    document.querySelectorAll('[data-theme-option]').forEach((option) => {
        option.setAttribute('aria-checked', String(option.dataset.themeOption === theme));
    });
};

document.querySelectorAll('[data-theme-option]').forEach((option) => {
    option.addEventListener('click', () => {
        const theme = option.dataset.themeOption;

        try {
            theme === 'system' ? localStorage.removeItem('theme') : localStorage.setItem('theme', theme);
        } catch {
            // Storage can be blocked. The choice then lasts for this page only.
        }

        applyTheme(theme);
    });
});

themeQuery.addEventListener('change', () => applyTheme(storedTheme()));
applyTheme(storedTheme());

window.demo = {
    async post(url, data = {}) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(data),
        });

        return { status: response.status, body: await response.json() };
    },

    /**
     * Show a "thinking" bubble at the end of a message list, scrolled into view.
     * Returns a function that removes it.
     */
    thinking(container, label = 'Thinking') {
        container.querySelector('[data-empty-state]')?.remove();
        container.insertAdjacentHTML('beforeend', `
            <div role="status" class="flex items-center gap-2.5 self-start rounded-2xl rounded-bl-md bg-tint px-4 py-3 text-sm text-muted">
                ${dots}<span>${label}</span>
            </div>
        `);

        const indicator = container.lastElementChild;
        container.scrollTop = container.scrollHeight;

        return () => indicator.remove();
    },

    /**
     * Put a button into a loading state with a spinner and a label.
     * Returns a function that restores the given label.
     */
    loading(button, label) {
        button.innerHTML = `${spinner}<span>${label}</span>`;

        return (restored) => {
            button.textContent = restored;
        };
    },

    /**
     * Keep a submit button disabled while its field is empty or a request is in flight.
     * Call sync() after setting the field value in code, and busy() around a request.
     */
    requireInput(field, button) {
        let busy = false;
        const sync = () => {
            button.disabled = busy || field.value.trim() === '';
        };

        field.addEventListener('input', sync);
        sync();

        return {
            sync,
            busy(value) {
                busy = value;
                sync();
            },
        };
    },

    inspect({ original = null, sent = null, blocked = false } = {}) {
        const root = document.querySelector('[data-inspector]');

        if (!root) {
            return;
        }

        root.querySelector('[data-inspector-original]').textContent = original ?? 'Nothing yet.';
        root.querySelector('[data-inspector-sent]').textContent = blocked
            ? 'Blocked. Nothing was sent to the provider.'
            : (sent ?? 'Nothing yet.');

        const badge = root.querySelector('[data-inspector-status]');

        if (original === null) {
            setPill(badge, 'neutral', 'waiting');
        } else if (blocked) {
            setPill(badge, 'bad', 'blocked');
        } else if (sent !== null && original.trim() !== sent.trim()) {
            setPill(badge, 'warn', 'modified');
        } else {
            setPill(badge, 'good', 'unchanged');
        }
    },

    inspectApproval({ scanned = [], interceptLog = [], toolsRun = [], blocked = false, detail = null } = {}) {
        const root = document.querySelector('[data-approval-inspector]');

        if (!root) {
            return;
        }

        const proposal = root.querySelector('[data-proposal-findings]');
        const segments = root.querySelector('[data-decision-segments]');

        const guardRecords = interceptLog.filter((record) => record.source === 'pending_approvals');
        const decisionRecords = interceptLog.filter((record) => record.source === 'approval_decisions');

        const describeEntities = (record) => {
            const entities = Object.entries(record.entities ?? {});

            return entities.length
                ? `entities: ${entities.map(([type, count]) => `${type} ×${count}`).join(', ')}`
                : null;
        };

        // Panel 1: what ToolApprovalGuard made of the proposed tool call.
        if (guardRecords.length) {
            proposal.textContent = guardRecords
                .map((record) => [
                    record.message,
                    ...record.findings.map(
                        (f) => `  ${f.type}  ${f.tool}${f.field ? `.${f.field}` : ''}${f.detail ? `  (${f.detail})` : ''}`
                    ),
                ].filter(Boolean).join('\n'))
                .join('\n\n');
        } else {
            proposal.textContent = blocked && !decisionRecords.length
                ? (detail ?? 'Blocked before the proposal reached you.')
                : 'Nothing flagged. The proposal reached review as-is.';
        }

        // Panel 2: what the human typed back, and what the prompt guards did with it.
        const segmentLines = scanned.length
            ? scanned.map((s) => `${s.toolCallId} → ${s.field}\n${s.text}`).join('\n\n')
            : 'Nothing typed this turn. Approving as-is sends no new text.';

        if (decisionRecords.length) {
            const notes = decisionRecords
                .map((record) => [
                    record.message,
                    describeEntities(record),
                    record.degradedFrom ? `degraded_from: ${record.degradedFrom}` : null,
                ].filter(Boolean).join('\n'))
                .join('\n\n');

            segments.textContent = `${segmentLines}\n\n${notes}`;
        } else if (blocked && detail && guardRecords.length === 0) {
            segments.textContent = `${segmentLines}\n\n${detail}`;
        } else {
            segments.textContent = segmentLines;
        }

        // Panel 3: the tools the SDK actually executed on this turn.
        root.querySelector('[data-tools-run]').textContent = toolsRun.length
            ? toolsRun.map((run) => `${run.tool}\n${run.result}`).join('\n\n')
            : (blocked ? 'No tool ran. The run stopped first.' : 'No tool ran this turn.');

        const badge = root.querySelector('[data-approval-status]');

        if (blocked) {
            setPill(badge, 'bad', 'blocked');
        } else if (interceptLog.length) {
            setPill(badge, 'warn', 'flagged, run continued');
        } else if (scanned.length) {
            setPill(badge, 'good', 'clean');
        } else {
            setPill(badge, 'neutral', 'nothing flagged');
        }
    },
};
