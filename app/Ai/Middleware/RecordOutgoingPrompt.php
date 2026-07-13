<?php

namespace App\Ai\Middleware;

use App\Ai\Support\PromptInspector;
use Closure;
use Laravel\Ai\Prompts\AgentPrompt;

class RecordOutgoingPrompt
{
    /**
     * Handle the incoming prompt.
     *
     * This middleware should run last so it captures the prompt exactly as
     * it leaves the pipeline for the AI provider.
     */
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        resolve(PromptInspector::class)->record($prompt);

        return $next($prompt);
    }
}
