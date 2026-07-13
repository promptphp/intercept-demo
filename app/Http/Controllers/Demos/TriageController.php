<?php

namespace App\Http\Controllers\Demos;

use App\Ai\Agents\TriageAgent;
use App\Ai\Support\PromptInspector;
use App\Http\Controllers\Controller;
use App\Models\InboundEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TriageController extends Controller
{
    public function index(): View
    {
        return view('demos.triage', [
            'emails' => InboundEmail::oldest('id')->get(),
        ]);
    }

    public function store(InboundEmail $inboundEmail, PromptInspector $inspector): JsonResponse
    {
        $prompt = $inboundEmail->toTriagePrompt();

        $response = (new TriageAgent)->prompt($prompt);

        $inboundEmail->update([
            'category' => $response['category'],
            'priority' => $response['priority'],
            'summary' => $response['summary'],
            'triaged_at' => now(),
        ]);

        return response()->json([
            'email' => $inboundEmail->only(['id', 'category', 'priority', 'summary']),
            'originalPrompt' => $prompt,
            'sentPrompt' => $inspector->finalPrompt,
        ]);
    }

    public function reset(): RedirectResponse
    {
        InboundEmail::query()->update([
            'category' => null,
            'priority' => null,
            'summary' => null,
            'triaged_at' => null,
        ]);

        return redirect()->route('demos.triage');
    }
}
