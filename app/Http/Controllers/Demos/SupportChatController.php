<?php

namespace App\Http\Controllers\Demos;

use App\Ai\Agents\SupportAgent;
use App\Ai\Support\PromptInspector;
use App\Http\Controllers\Controller;
use App\Http\Requests\Demos\SendSupportMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use PromptPHP\Intercept\InjectionGuard\Exceptions\PromptInjectionGuardException;
use PromptPHP\Intercept\PIIRedactor\Exceptions\PIIRedactorException;

class SupportChatController extends Controller
{
    public function show(): View
    {
        return view('demos.support');
    }

    public function store(SendSupportMessageRequest $request, PromptInspector $inspector): JsonResponse
    {
        try {
            $response = (new SupportAgent)->prompt($request->validated('message'));
        } catch (PromptInjectionGuardException) {
            return response()->json([
                'blocked' => true,
                'reason' => 'Prompt injection detected. The message never reached the AI provider.',
            ], 422);
        } catch (PIIRedactorException) {
            return response()->json([
                'blocked' => true,
                'reason' => 'Card numbers and secrets are never sent. The message was blocked.',
            ], 422);
        }

        return response()->json([
            'reply' => $response->text,
            'sentPrompt' => $inspector->finalPrompt,
        ]);
    }
}
