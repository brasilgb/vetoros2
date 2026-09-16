<?php

namespace App\Http\Requests\Budgets;

use App\Enums\BudgetItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(BudgetItemType::class)],
            'description' => ['required', 'string'],
            'quantity' => ['required', 'string'],
            'unit_price' => ['required', 'string'],
            'discount_amount' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
