<?php

namespace App\Http\Requests\Demos;

use App\Ai\Agents\ApprovalAgent;

/**
 * Shared handling for the approval desk's middleware toggles.
 *
 * Both endpoints build the same agent, because the guard inspects every response — the one
 * that first pauses for approval and any further proposal the model makes after resuming.
 */
trait ResolvesGuardOptions
{
    /**
     * Get the validation rules for the middleware toggles.
     *
     * @return array<string, array<int, string>>
     */
    protected function guardOptionRules(): array
    {
        return [
            'scanDecisions' => ['nullable', 'boolean'],
            'guardProposals' => ['nullable', 'boolean'],
            'scanAllEntities' => ['nullable', 'boolean'],
            'scanInjection' => ['nullable', 'boolean'],
            'denyEmailTool' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Build the agent the demo page's current toggle state describes.
     */
    public function agent(): ApprovalAgent
    {
        return new ApprovalAgent(
            scanApprovalDecisions: $this->toggle('scanDecisions'),
            guardProposals: $this->toggle('guardProposals'),
            scanAllEntities: $this->toggle('scanAllEntities', default: false),
            scanInjection: $this->toggle('scanInjection', default: false),
            deniedTools: $this->toggle('denyEmailTool', default: false) ? ['SendCustomerEmail'] : [],
        );
    }

    /**
     * Read a toggle, treating an absent value as the shipped default.
     */
    protected function toggle(string $key, bool $default = true): bool
    {
        return (bool) ($this->validated($key) ?? $default);
    }
}
