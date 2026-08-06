<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Concerns\InteractsWithApprovals;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Sends text to a customer, so a human reads it first. This is also the tool an
 * operator is most likely to edit on the approval desk, which is exactly the text
 * Intercept scans when the run resumes.
 */
class SendCustomerEmail implements Approvable, Tool
{
    use InteractsWithApprovals;

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Email a customer about their order. Requires approval before it is sent.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return sprintf(
            'Sent "%s" to %s.',
            $request['subject'],
            $request['to'],
        );
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'to' => $schema->string()->description('The customer email address.')->required(),
            'subject' => $schema->string()->description('The email subject line.')->required(),
            'body' => $schema->string()->description('The body of the email.')->required(),
        ];
    }
}
