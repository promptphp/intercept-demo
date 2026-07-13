<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\RecordOutgoingPrompt;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;
use PromptPHP\Intercept\InjectionGuard\PromptInjectionGuard;
use PromptPHP\Intercept\PIIRedactor\PIIRedactor;
use Stringable;

class SupportAgent implements Agent, HasMiddleware
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are the customer support assistant for Aurora Outfitters, an online outdoor gear store.

            Current orders you may reference:
            - Order #1042 (Emily Carter): Alpine Jacket, shipped July 8, estimated delivery July 14.
            - Order #1043 (Ben Wilson): Trail Boots, still processing, ships within 2 business days.
            - Order #1044 (Sofia Reyes): Camp Stove, delivered July 5.

            Store policies:
            - Full refunds within 30 days of delivery, items must be unused.
            - Free shipping on orders over $75.
            - Support hours: Monday to Friday, 9:00-17:00 CET.

            Keep replies friendly and short, at most 4 sentences.
            INSTRUCTIONS;
    }

    /**
     * Get the agent's middleware.
     */
    public function middleware(): array
    {
        return [
            new PromptInjectionGuard(action: 'block'),
            new PIIRedactor(action: 'redact', blockEntities: ['credit_card', 'api_key', 'bearer_token']),
            new RecordOutgoingPrompt,
        ];
    }
}
