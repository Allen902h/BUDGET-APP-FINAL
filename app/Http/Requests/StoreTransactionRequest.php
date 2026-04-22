<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'cycle_id' => [
                'required',
                'integer',
                Rule::exists('income_cycles', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'transaction_type' => ['required', 'in:income,expense'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', auth()->id())),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'timestamp' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('transaction_type');
            $hasCategory = $this->filled('category_id');

            if ($type === 'expense' && ! $hasCategory) {
                $validator->errors()->add('category_id', 'An expense transaction requires a category.');
            }

            if ($type === 'income' && $hasCategory) {
                $validator->errors()->add('category_id', 'Income transactions should not include a category.');
            }
        });
    }
}
