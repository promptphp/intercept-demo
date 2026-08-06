<?php

namespace App\Http\Requests\Demos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Laravel\Ai\Approvals\Decision;
use Laravel\Ai\Approvals\Decisions;

class ResolveApprovalsRequest extends FormRequest
{
    use ResolvesGuardOptions;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'conversationId' => ['required', 'string'],
            'decisions' => ['required', 'array', 'min:1'],
            'decisions.*.action' => ['required', 'string', Rule::in(['approve', 'reject', 'edit'])],
            'decisions.*.result' => ['nullable', 'string', 'max:2000'],
            'decisions.*.arguments' => ['nullable', 'array'],
            'decisions.*.arguments.*' => ['nullable', 'string', 'max:2000'],
            ...$this->guardOptionRules(),
        ];
    }

    /**
     * Build the SDK decisions that resolve the paused run.
     *
     * Every value in here was typed by a human on the approval desk, which is exactly
     * the content Intercept scans once the run resumes.
     */
    public function decisions(): Decisions
    {
        return Decisions::from(
            collect($this->validated('decisions'))
                ->map(fn (array $decision): Decision => match ($decision['action']) {
                    'approve' => Decision::approve(),
                    'reject' => Decision::reject($decision['result'] ?? null),
                    'edit' => Decision::edit($decision['arguments'] ?? []),
                })
                ->all()
        );
    }
}
