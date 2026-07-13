<?php

namespace App\Ai\Support;

use Laravel\Ai\Prompts\AgentPrompt;

/**
 * Captures the final prompt that leaves the middleware pipeline so demo
 * pages can show exactly what was sent to the AI provider.
 */
class PromptInspector
{
    public ?string $finalPrompt = null;

    public ?string $agent = null;

    public ?string $model = null;

    public function record(AgentPrompt $prompt): void
    {
        $this->finalPrompt = $prompt->prompt;
        $this->agent = class_basename($prompt->agent);
        $this->model = $prompt->model;
    }
}
