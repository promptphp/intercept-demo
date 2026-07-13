<?php

namespace App\Models;

use Database\Factories\InboundEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InboundEmail extends Model
{
    /** @use HasFactory<InboundEmailFactory> */
    use HasFactory;

    protected $fillable = [
        'from_name',
        'from_email',
        'subject',
        'body',
        'category',
        'priority',
        'summary',
        'triaged_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'triaged_at' => 'datetime',
        ];
    }

    /**
     * Get the email formatted as a prompt for the triage agent.
     */
    public function toTriagePrompt(): string
    {
        return implode(PHP_EOL, [
            "From: {$this->from_name} <{$this->from_email}>",
            "Subject: {$this->subject}",
            '',
            $this->body,
        ]);
    }
}
