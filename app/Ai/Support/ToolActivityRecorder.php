<?php

namespace App\Ai\Support;

use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Tools\ToolNameResolver;

/**
 * Records the tools that actually ran during the current request.
 *
 * A blocked approval decision leaves no trace in the reply, because the SDK never applies it.
 * This list is the evidence: Intercept scans the decisions before the SDK runs the approved
 * or edited tool call, so a blocked turn records no tool at all.
 */
class ToolActivityRecorder
{
    /**
     * The tools that ran, in order.
     *
     * @var array<int, array{tool: string, result: string}>
     */
    public array $runs = [];

    /**
     * Record a tool the SDK executed.
     */
    public function capture(ToolInvoked $event): void
    {
        $this->runs[] = [
            'tool' => ToolNameResolver::resolve($event->tool),
            'result' => str((string) $event->result)->limit(200)->toString(),
        ];
    }
}
