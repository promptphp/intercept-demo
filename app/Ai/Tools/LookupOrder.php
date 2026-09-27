<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Reads an order. Deliberately not approvable: only the tools that change
 * something or leave the building need a human in front of them.
 *
 * The result carries the full card on file, as a careless internal API might. Intercept
 * does not scan tool results, so the card reaches the model. If the model then copies it
 * into an email, ToolApprovalGuard catches the proposal before anyone can approve it.
 */
class LookupOrder implements Tool
{
    /**
     * The orders the demo store knows about.
     *
     * @var array<string, array{customer: string, item: string, status: string, total: string, payment: string}>
     */
    protected const ORDERS = [
        '1042' => ['customer' => 'Emily Carter', 'item' => 'Alpine Jacket', 'status' => 'shipped July 8, estimated delivery July 14', 'total' => '$189.00', 'payment' => 'Visa 4111 1111 1111 1111'],
        '1043' => ['customer' => 'Ben Wilson', 'item' => 'Trail Boots', 'status' => 'processing, ships within 2 business days', 'total' => '$154.00', 'payment' => 'Mastercard 5555 5555 5555 4444'],
        '1044' => ['customer' => 'Sofia Reyes', 'item' => 'Camp Stove', 'status' => 'delivered July 5', 'total' => '$92.50', 'payment' => 'Visa 4012 8888 8888 1881'],
    ];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up the current status, customer, total, and payment card of an order by its number.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $order = self::ORDERS[(string) $request['order_id']] ?? null;

        if ($order === null) {
            return "No order found with the number {$request['order_id']}.";
        }

        return implode(' ', [
            "Order #{$request['order_id']} for {$order['customer']}:",
            "{$order['item']}, {$order['total']}, {$order['status']}.",
            "Paid with {$order['payment']}.",
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->string()->description('The order number, without the leading hash.')->required(),
        ];
    }
}
