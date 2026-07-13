<?php

namespace App\Http\Controllers\Demos;

use App\Ai\Agents\DebugAgent;
use App\Ai\Support\PromptInspector;
use App\Http\Controllers\Controller;
use App\Http\Requests\Demos\AnalyzeLogRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use PromptPHP\Intercept\PIIRedactor\Exceptions\PIIRedactorException;

class LogDebuggerController extends Controller
{
    public function show(): View
    {
        return view('demos.debugger');
    }

    public function store(AnalyzeLogRequest $request, PromptInspector $inspector): JsonResponse
    {
        try {
            $response = (new DebugAgent)->prompt($request->validated('log'));
        } catch (PIIRedactorException) {
            return response()->json([
                'blocked' => true,
                'reason' => 'The log contains a secret (API key or bearer token) and was blocked before leaving your server.',
            ], 422);
        }

        return response()->json([
            'reply' => $response->text,
            'sentPrompt' => $inspector->finalPrompt,
        ]);
    }
}
