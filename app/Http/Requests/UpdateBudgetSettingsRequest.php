<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'currency_pref' => ['required', 'string', Rule::in(array_keys(config('currencies.supported', [])))],
            'savings_goal_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'monthly_budget_limit' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
