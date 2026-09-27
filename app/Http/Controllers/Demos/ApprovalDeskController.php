<?php

namespace App\Http\Controllers\Demos;

use App\Ai\Support\InterceptLogRecorder;
use App\Ai\Support\PromptInspector;
use App\Ai\Support\ToolActivityRecorder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Demos\ResolveApprovalsRequest;
use App\Http\Requests\Demos\StartApprovalRunRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Models\ConversationMessage;
use Laravel\Ai\Responses\AgentResponse;
use PromptPHP\Intercept\InjectionGuard\Exceptions\PromptInjectionGuardException;
use PromptPHP\Intercept\PIIRedactor\Exceptions\PIIRedactorException;
use PromptPHP\Intercept\ToolApprovalGuard\Exceptions\ToolApprovalGuardException;

class ApprovalDeskController extends Controller
{
    public function show(): View
    {
        return view('demos.approvals');
    }

    /**
     * Start a run. The agent proposes a tool call and pauses for a human.
     *
     * `ToolApprovalGuard` can stop the run here, before the proposal is ever surfaced. The
     * operator does not get to approve something the guard refused to show them.
     *
     * The prompt guards can also stop the run here, on the operator's own message.
     */
    public function store(StartApprovalRunRequest $request, PromptInspector $inspector, InterceptLogRecorder $recorder): JsonResponse
    {
        try {
            $response = $request->agent()
                ->forUser($this->operator())
                ->prompt($request->validated('message'));
        } catch (ToolApprovalGuardException $e) {
            return $this->blocked(
                'The agent proposed a tool call that never made it to your review screen.',
                $e->getMessage(),
                $recorder,
                stillAwaitingApproval: false,
            );
        } catch (PromptInjectionGuardException) {
            return $this->blocked(
                'Your request contains a prompt injection. It never reached the provider.',
                'Prompt injection attempt detected.',
                $recorder,
                stillAwaitingApproval: false,
            );
        } catch (PIIRedactorException) {
            return $this->blocked(
                'Your request carries a card number or a secret. It never reached the provider.',
                'PII detected in agent prompt.',
                $recorder,
                stillAwaitingApproval: false,
            );
        }

        return response()->json($this->payload($response, $inspector, $recorder));
    }

    /**
     * Resume a paused run with the decisions a human made on the approval desk.
     *
     * Edited arguments and rejection notes are the only new content on this path. The SDK
     * applies them before the first step, so an approved or edited tool call runs before any
     * step middleware sees it. Intercept scans the decisions first, so a blocked resume runs
     * no tool and sends nothing to the provider.
     */
    public function resume(ResolveApprovalsRequest $request, PromptInspector $inspector, InterceptLogRecorder $recorder): JsonResponse
    {
        try {
            $response = $request->agent()
                ->continue($request->validated('conversationId'), as: $this->operator())
                ->prompt($request->decisions());
        } catch (ToolApprovalGuardException $e) {
            return $this->blocked(
                'Your decision was clean, but the tool call the agent proposed next was not.',
                $e->getMessage(),
                $recorder,
                stillAwaitingApproval: false,
            );
        } catch (PromptInjectionGuardException $e) {
            return $this->blocked(
                'What you typed contains a prompt injection. The run stopped before the tool ran.',
                $e->getMessage(),
                $recorder,
            );
        } catch (PIIRedactorException) {
            return $this->blocked(
                'What you typed carries a card number or a secret. The run stopped before the tool ran.',
                'PII detected in tool approval decisions.',
                $recorder,
            );
        }

        return response()->json($this->payload($response, $inspector, $recorder));
    }

    /**
     * Clear every conversation so the next take starts from a pristine desk.
     *
     * Conversations do not cascade to their messages, so both go.
     */
    public function reset(): RedirectResponse
    {
        ConversationMessage::query()->delete();
        Conversation::query()->delete();

        return redirect()->route('demos.approvals');
    }

    /**
     * Build the JSON payload describing where the run got to.
     *
     * @return array<string, mixed>
     */
    protected function payload(AgentResponse $response, PromptInspector $inspector, InterceptLogRecorder $recorder): array
    {
        return [
            'conversationId' => $response->conversationId,
            'status' => $response->hasPendingApprovals() ? 'awaiting_approval' : 'complete',
            'reply' => $response->text,
            'approvals' => $response->hasPendingApprovals()
                ? $response->pendingApprovals->map->toArray()->all()
                : [],
            'scanned' => $inspector->decisionSegments,
            'interceptLog' => $recorder->records,
            'toolsRun' => resolve(ToolActivityRecorder::class)->runs,
        ];
    }

    /**
     * Build the JSON payload for a run Intercept stopped.
     *
     * The exception message names the offending tool call and field but never the
     * matched text, so it is safe to put straight on the screen.
     */
    protected function blocked(string $reason, string $detail, InterceptLogRecorder $recorder, bool $stillAwaitingApproval = true): JsonResponse
    {
        return response()->json([
            'blocked' => true,
            'reason' => $reason,
            'detail' => $detail,
            'stillAwaitingApproval' => $stillAwaitingApproval,
            'interceptLog' => $recorder->records,
            'toolsRun' => resolve(ToolActivityRecorder::class)->runs,
        ], 422);
    }

    /**
     * Get the support lead working the approval desk.
     *
     * The demo has no authentication, so the seeded user stands in for whoever is
     * logged in. Tool approval needs a conversation participant to persist against.
     */
    protected function operator(): User
    {
        return User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password'],
        );
    }
}
