<?php

namespace App\Ai\Support;

use Laravel\Ai\PendingStep;
use Laravel\Ai\Prompts\AgentPrompt;
use PromptPHP\Intercept\Support\Concerns\InspectsPendingSteps;
use PromptPHP\Intercept\Support\Concerns\ScansApprovalDecisions;
use PromptPHP\Intercept\Support\ValueObjects\ApprovalDecisionSegment;

/**
 * Captures the first generation step that leaves the middleware pipeline so demo
 * pages can show exactly what was sent to the AI provider.
 *
 * The SDK runs agent middleware on every step of a run. Every step sends the same
 * prompt, so the inspector keeps the first step only.
 *
 * A prompt resuming a paused run carries no new prompt text, so there is nothing useful to
 * show in the "sent to provider" panel. The new content on that path is whatever a human
 * typed on the approval desk, which this class extracts using the same concern the
 * Intercept middleware use to find it.
 */
class PromptInspector
{
    use InspectsPendingSteps;
    use ScansApprovalDecisions;

    public ?string $finalPrompt = null;

    public ?string $agent = null;

    public ?string $model = null;

    /**
     * The operator-supplied text carried by a resumed run.
     *
     * @var array<int, array{toolCallId: string, field: string, text: string}>
     */
    public array $decisionSegments = [];

    /**
     * Record the first step of a run as it leaves for the provider.
     */
    public function recordStep(PendingStep $step): void
    {
        if (! $step->isFirstStep()) {
            return;
        }

        $this->finalPrompt = $this->startsNewTurn($step)
            ? (string) $this->latestUserMessage($step)?->content
            : '';

        $agent = $this->agentFor($step);

        $this->agent = $agent !== null ? class_basename($agent) : null;
        $this->model = $step->model;
    }

    /**
     * Record the operator-supplied text of a resumed run.
     */
    public function recordDecisions(AgentPrompt $prompt): void
    {
        $this->decisionSegments = array_map(
            fn (ApprovalDecisionSegment $segment): array => [
                'toolCallId' => $segment->toolCallId,
                'field' => $segment->field,
                'text' => $segment->text,
            ],
            $this->approvalDecisionSegments($prompt->approvalDecisions),
        );
    }

    /**
     * Determine whether the recorded prompt resumed a paused run.
     */
    public function resumedFromApproval(): bool
    {
        return $this->decisionSegments !== [];
    }
}
