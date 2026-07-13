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
};
