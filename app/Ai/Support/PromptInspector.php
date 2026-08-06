<?php

namespace App\Ai\Support;

use Laravel\Ai\Prompts\AgentPrompt;
use PromptPHP\Intercept\Support\Concerns\ScansApprovalDecisions;
use PromptPHP\Intercept\Support\ValueObjects\ApprovalDecisionSegment;

/**
 * Captures the final prompt that leaves the middleware pipeline so demo
 * pages can show exactly what was sent to the AI provider.
 *
 * A prompt resuming a paused run carries no prompt text, so there is nothing useful to
 * show in the "sent to provider" panel. The new content on that path is whatever a human
 * typed on the approval desk, which this class extracts using the same concern the
 * Intercept middleware use to find it.
 */
class PromptInspector
{
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

    public function record(AgentPrompt $prompt): void
    {
        $this->finalPrompt = $prompt->prompt;
        $this->agent = class_basename($prompt->agent);
        $this->model = $prompt->model;

        $this->decisionSegments = array_map(
            fn (ApprovalDecisionSegment $segment): array => [
                'toolCallId' => $segment->toolCallId,
                'field' => $segment->field,
                'text' => $segment->text,
            ],
            $prompt->hasApprovalDecisions()
                ? $this->approvalDecisionSegments($prompt->approvalDecisions)
                : [],
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
