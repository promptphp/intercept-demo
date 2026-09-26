<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Migrations\AiMigration;

/**
 * laravel/ai v1.0 stores each message's generation steps and a status, not separate tool
 * call, tool result, and approval columns. The old columns can not convert to steps, so the
 * migration keeps earlier rows as completed messages with no steps.
 */
return new class extends AiMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

        Schema::table($messagesTable, function (Blueprint $table) {
            $table->dropIndex('participant_index');
            $table->dropColumn(['tool_calls', 'tool_results', 'approval_state']);
        });

        Schema::table($messagesTable, function (Blueprint $table) {
            $table->longText('steps')->after('attachments');
            $table->string('status', 25)->after('meta');

            $table->index(['participant_type', 'participant_id', 'agent'], 'participant_index');
        });

        DB::table($messagesTable)->update(['steps' => '[]', 'status' => 'completed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

        Schema::table($messagesTable, function (Blueprint $table) {
            $table->dropIndex('participant_index');
            $table->dropColumn(['steps', 'status']);
        });

        Schema::table($messagesTable, function (Blueprint $table) {
            $table->text('tool_calls')->after('attachments');
            $table->text('tool_results')->after('tool_calls');
            $table->text('approval_state')->nullable()->after('meta');

            $table->index(['participant_type', 'participant_id'], 'participant_index');
        });

        DB::table($messagesTable)->update(['tool_calls' => '[]', 'tool_results' => '[]']);
    }
};
