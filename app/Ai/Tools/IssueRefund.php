<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Approvals\Approval;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Moves money, so it never executes without a human decision.
 */
class IssueRefund implements Approvable, Tool
{
    use InteractsWithApprovals;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Refund an order. Requires approval from a support lead before it executes.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return sprintf(
            'Refunded %s on order #%s. Reason recorded: %s',
            $request['amount'],
            $request['order_id'],
            $request['reason'],
        );
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->description('The order number to refund.')->required(),
            'amount' => $schema->string()->description('The amount to refund, including the currency symbol.')->required(),
            'reason' => $schema->string()->description('Why the refund is being issued.')->required(),
        ];
    }

    /**
     * Determine whether the tool needs approval for the given request.
     *
     * Small goodwill refunds run themselves; anything larger waits for a human.
     */
    protected function needsApproval(Request $request): Approval|bool
    {
        $amount = (float) ltrim((string) $request['amount'], '$');

        return $amount <= 25.00
            ? false
            : Approval::required('Refunds over $25 need a support lead to sign off.');
    }
}
