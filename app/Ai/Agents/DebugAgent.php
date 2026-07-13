<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\RecordOutgoingPrompt;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;
use PromptPHP\Intercept\PIIRedactor\PIIRedactor;
use Stringable;

class DebugAgent implements Agent, HasMiddleware
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are a senior Laravel engineer helping teammates debug production issues from logs and stack traces.

            Analyze the provided log output and answer with exactly three short sections:
            1. Root cause: what went wrong and why.
            2. Fix: the concrete change to make.
            3. Prevention: how to stop this class of bug from recurring.

            Note that sensitive values in the log may be masked or redacted. Be concise and practical.
            INSTRUCTIONS;
    }

    /**
     * Get the agent's middleware.
     *
     * Developers need masked values (like partial IPs) to correlate log lines,
     * but secrets must never reach a third-party provider, so API keys and
     * bearer tokens hard-block the request.
     */
    public function middleware(): array
    {
        return [
            new PIIRedactor(action: 'mask', blockEntities: ['api_key', 'bearer_token']),
            new RecordOutgoingPrompt,
        ];
    }
}
