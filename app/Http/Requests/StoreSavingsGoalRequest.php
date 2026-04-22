<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('savings_goals', 'name')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
