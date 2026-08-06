<?php

namespace App\Ai\Support;

use Illuminate\Log\Events\MessageLogged;

/**
 * Captures what the Intercept middleware wrote to the log during the current request.
 *
 * Some middleware outcomes are invisible in the prompt itself. A resumed run cannot be
 * rewritten, so `redact` and `mask` degrade to logging rather than changing anything the
 * inspector could show, and a blocked proposal never becomes a prompt at all. The log entry
 * is the only evidence the scan happened, so the approval desk surfaces it directly.
 *
 * Both tool approval paths tag their context with a `source`, which is a far steadier hook
 * than matching on the log message:
 *
 * - `pending_approvals` — ToolApprovalGuard, on what the model proposed.
 * - `approval_decisions` — InjectionGuard and PIIRedactor, on what the human typed back.
 */
class InterceptLogRecorder
{
    /**
     * The log sources this recorder cares about.
     *
     * @var array<int, string>
     */
    protected const SOURCES = ['pending_approvals', 'approval_decisions'];

    /**
     * The Intercept log records captured so far.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $records = [];

    /**
     * Record an Intercept log entry, ignoring everything else the app logs.
     */
    public function capture(MessageLogged $event): void
    {
        $source = $event->context['source'] ?? null;

        if (! in_array($source, self::SOURCES, true)) {
            return;
        }

        $this->records[] = [
            'source' => $source,
            'message' => $event->message,
            'entities' => $event->context['entities'] ?? [],
            'findings' => $this->findings($event->context),
            'degradedFrom' => $event->context['degraded_from'] ?? null,
            'segments' => $event->context['segments'] ?? [],
        ];
    }

    /**
     * Summarise the guard's findings for display.
     *
     * The matched value is only ever present as a hash, so nothing sensitive is echoed back
     * to the browser here.
     *
     * @param  array<string, mixed>  $context
     * @return array<int, array{tool: string, type: string, field: string|null, detail: string|null}>
     */
    protected function findings(array $context): array
    {
        return array_map(fn (array $finding): array => [
            'tool' => $finding['tool'] ?? '',
            'type' => $finding['type'] ?? '',
            'field' => $finding['field'] ?? null,
            'detail' => $finding['detail'] ?? null,
        ], $context['findings'] ?? []);
    }

    /**
     * Determine whether any Intercept middleware logged during this request.
     */
    public function hasRecords(): bool
    {
        return $this->records !== [];
    }
}
