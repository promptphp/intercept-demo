<?php

namespace App\Ai\Middleware;

use App\Ai\Support\PromptInspector;
use Closure;
use Laravel\Ai\PendingStep;
use Laravel\Ai\Prompts\AgentPrompt;
use PromptPHP\Intercept\Support\Contracts\InspectsApprovalDecisions;

class RecordOutgoingPrompt implements InspectsApprovalDecisions
{
    /**
     * Handle a generation step.
     *
     * This middleware should run last so it captures the step exactly as
     * it leaves the pipeline for the AI provider.
     */
    public function handle(PendingStep $step, Closure $next): mixed
    {
        resolve(PromptInspector::class)->recordStep($step);

        return $next($step);
    }

    /**
     * Record the approval decisions of a resumed run.
     *
     * Intercept calls this before the SDK applies the decisions. It runs after the
     * guards listed ahead of it, so a blocked decision is never recorded.
     */
    public function inspectApprovalDecisions(AgentPrompt $prompt): void
    {
        resolve(PromptInspector::class)->recordDecisions($prompt);
    }
}
