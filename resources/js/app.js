const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

    inspect({ original = null, sent = null, blocked = false } = {}) {
        const root = document.querySelector('[data-inspector]');

        if (!root) {
            return;
        }

        root.querySelector('[data-inspector-original]').textContent = original ?? '—';
        root.querySelector('[data-inspector-sent]').textContent = blocked
            ? 'Blocked by middleware — nothing was sent to the provider.'
            : (sent ?? '—');

        const state = blocked
            ? 'blocked'
            : (original !== null && sent !== null && original.trim() !== sent.trim() ? 'modified' : 'unchanged');

        const badge = root.querySelector('[data-inspector-status]');
        const styles = {
            unchanged: 'bg-emerald-500/15 text-emerald-400',
            modified: 'bg-amber-500/15 text-amber-400',
            blocked: 'bg-red-500/15 text-red-400',
        };
        const labels = {
            unchanged: 'passed through unchanged',
            modified: 'modified by middleware',
            blocked: 'blocked',
        };

        badge.className = `rounded-full px-2.5 py-1 text-xs font-medium ${styles[state]}`;
        badge.textContent = labels[state];
    },

    inspectApproval({ scanned = [], interceptLog = [], blocked = false, detail = null } = {}) {
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
                ? `entities: ${entities.map(([type, count]) => `${type} \u00d7${count}`).join(', ')}`
                : null;
        };

        // Panel 1 — what ToolApprovalGuard made of the proposed tool call.
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
                ? (detail ?? 'Blocked before the proposal was surfaced.')
                : 'Nothing flagged — the proposal was surfaced for review as-is.';
        }

        // Panel 2 — what the human typed back, and what the prompt-side middleware did with it.
        const segmentLines = scanned.length
            ? scanned.map((s) => `${s.toolCallId} \u2192 ${s.field}\n${s.text}`).join('\n\n')
            : 'Nothing operator-supplied on this turn — approving as-is carries no new text.';

        if (decisionRecords.length) {
            const notes = decisionRecords
                .map((record) => [
                    record.message,
                    describeEntities(record),
                    record.degradedFrom
                        ? `degraded_from: ${record.degradedFrom}  (a resumed prompt cannot be rewritten)`
                        : null,
                ].filter(Boolean).join('\n'))
                .join('\n\n');

            segments.textContent = `${segmentLines}\n\n${notes}`;
        } else if (blocked && detail && guardRecords.length === 0) {
            segments.textContent = `${segmentLines}\n\n${detail}`;
        } else {
            segments.textContent = segmentLines;
        }

        const state = blocked
            ? 'blocked'
            : (interceptLog.length ? 'flagged' : (scanned.length ? 'clean' : 'idle'));

        const badge = root.querySelector('[data-approval-status]');
        const styles = {
            idle: 'bg-zinc-800 text-zinc-400',
            clean: 'bg-emerald-500/15 text-emerald-400',
            flagged: 'bg-amber-500/15 text-amber-400',
            blocked: 'bg-red-500/15 text-red-400',
        };
        const labels = {
            idle: 'nothing flagged',
            clean: 'scanned — clean',
            flagged: 'flagged — logged, run continued',
            blocked: 'blocked — run stopped',
        };

        badge.className = `rounded-full px-2.5 py-1 text-xs font-medium ${styles[state]}`;
        badge.textContent = labels[state];
    },
};
