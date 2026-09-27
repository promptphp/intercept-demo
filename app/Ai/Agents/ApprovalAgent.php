<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\RecordOutgoingPrompt;
use App\Ai\Tools\IssueRefund;
use App\Ai\Tools\LookupOrder;
use App\Ai\Tools\SendCustomerEmail;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use PromptPHP\Intercept\InjectionGuard\PromptInjectionGuard;
use PromptPHP\Intercept\PIIRedactor\PIIRedactor;
use PromptPHP\Intercept\ToolApprovalGuard\ToolApprovalGuard;
use Stringable;

class ApprovalAgent implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * The entities that are never a legitimate value in one of this agent's tool arguments.
     *
     * This is also the guard's shipped default, so the middleware below does not pass it —
     * the default is the demonstration. Widening it is what the desk's toggle does, because
     * an email address in `SendCustomerEmail(to:)` is the tool's own parameter, not a leak.
     *
     * @var array<int, string>
     */
    protected const SECRET_ENTITIES = ['credit_card', 'api_key', 'bearer_token'];

    /**
     * Every entity the redactor can detect. The widen toggle uses it to show why the default is narrow.
     *
     * @var array<int, string>
     */
    protected const ALL_ENTITIES = [
        'email', 'phone', 'credit_card', 'ip_address',
        'api_key', 'bearer_token', 'mac_address', 'url',
    ];

    /**
     * Create a new approval agent.
     *
     * Every flag here exists so the demo page can toggle one guarantee at a time. Real agents
     * hard-code their policy; none of this is a pattern to copy.
     *
     * @param  bool  $scanApprovalDecisions  Whether Intercept scans what the support lead typed,
     *                                       before the SDK runs the approved tool call.
     * @param  bool  $guardProposals  Whether Intercept inspects what the model proposed,
     *                                before the proposal reaches the review screen.
     * @param  bool  $scanAllEntities  Whether to widen the guard past its narrow default to every
     *                                 detectable entity, which shows why the default is narrow.
     * @param  bool  $scanInjection  Whether to scan proposed arguments for injection patterns.
     *                               Off by default in the package, for the same reason.
     * @param  array<int, string>  $deniedTools  Tools the guard refuses outright.
     */
    public function __construct(
        protected bool $scanApprovalDecisions = true,
        protected bool $guardProposals = true,
        protected bool $scanAllEntities = false,
        protected bool $scanInjection = false,
        protected array $deniedTools = [],
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
            You are the back-office operations assistant for Aurora Outfitters, an online outdoor gear store.

            You work a queue of customer requests. Look up orders before acting on them, then use the
            tools available to you to resolve the request.

            Store policies:
            - Full refunds within 30 days of delivery, items must be unused.
            - Refunds over $25 need a support lead to approve them before they are issued.
            - Always tell the customer what you did, in a short and friendly email.

            Be decisive: propose the concrete tool call you believe resolves the request, and let the
            support lead approve it. Do not ask the customer for permission to do your job.
            INSTRUCTIONS;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new LookupOrder,
            new IssueRefund,
            new SendCustomerEmail,
        ];
    }

    /**
     * Get the agent's middleware.
     *
     * This agent guards both ends of one human-in-the-loop pause.
     *
     * The SDK runs this middleware on every generation step.
     *
     * `ToolApprovalGuard` is the only middleware here that acts on the step response, because the
     * tool calls it inspects are proposed by the model. It runs before a proposal is ever surfaced
     * for review. Intercept does not scan tool results, such as the card on file that `LookupOrder`
     * returns, so this is the first point where a leak from them can be caught.
     *
     * The other two act on the prompt and on every user message in the history. On a resumed run
     * they scan what the support lead typed: edited tool arguments and rejection notes. Intercept
     * scans those before the SDK applies them, so a block stops the tool before it runs. Decisions
     * can not be rewritten, so `redact` degrades to logging there, while blocked entities and the
     * injection guard's `block` still stop the run.
     */
    public function middleware(): array
    {
        return [
            ...($this->guardProposals ? [$this->toolApprovalGuard()] : []),
            new PromptInjectionGuard(action: 'block', scanApprovalDecisions: $this->scanApprovalDecisions),
            new PIIRedactor(action: 'redact', blockEntities: self::SECRET_ENTITIES, scanApprovalDecisions: $this->scanApprovalDecisions),
            new RecordOutgoingPrompt,
        ];
    }

    /**
     * Build the guard that inspects what the model proposed.
     *
     * Passing null for `entities` and `scanInjection` takes the shipped defaults, which are
     * deliberately narrow: only values that are never a legitimate tool argument, and no
     * injection scanning. Both toggles here widen past that so the desk can show what the
     * broader settings cost — they flag ordinary mail traffic, not attacks.
     */
    protected function toolApprovalGuard(): ToolApprovalGuard
    {
        return new ToolApprovalGuard(
            action: 'block',
            deniedTools: $this->deniedTools,
            entities: $this->scanAllEntities ? self::ALL_ENTITIES : null,
            // Null leaves the config in charge; only opting in overrides it.
            scanInjection: $this->scanInjection ? true : null,
        );
    }
}
