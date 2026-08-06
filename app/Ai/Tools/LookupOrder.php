<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Reads an order. Deliberately not approvable: only the tools that change
 * something or leave the building need a human in front of them.
 */
class LookupOrder implements Tool
{
    /**
     * The orders the demo store knows about.
     *
     * @var array<string, array{customer: string, item: string, status: string, total: string}>
     */
    protected const ORDERS = [
        '1042' => ['customer' => 'Emily Carter', 'item' => 'Alpine Jacket', 'status' => 'shipped July 8, estimated delivery July 14', 'total' => '$189.00'],
        '1043' => ['customer' => 'Ben Wilson', 'item' => 'Trail Boots', 'status' => 'processing, ships within 2 business days', 'total' => '$154.00'],
        '1044' => ['customer' => 'Sofia Reyes', 'item' => 'Camp Stove', 'status' => 'delivered July 5', 'total' => '$92.50'],
    ];

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up the current status, customer, and total of an order by its number.';
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
