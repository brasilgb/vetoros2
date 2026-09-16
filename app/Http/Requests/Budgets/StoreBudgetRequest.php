<?php

namespace App\Http\Requests\Budgets;

use App\Enums\BudgetItemType;
use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = Tenant::current()?->getKey();

        return [
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'customer_id' => ['required_without:order_id', 'nullable', 'integer', Rule::exists('customers', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'company_id' => ['required_without:order_id', 'nullable', 'integer', Rule::exists('companies', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'budget_template_id' => ['nullable', 'integer', Rule::exists('budget_templates', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId)->where('active', true))],
            'discount_amount' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.type' => ['required_with:items', Rule::enum(BudgetItemType::class)],
            'items.*.description' => ['required_with:items', 'string'],
            'items.*.quantity' => ['required_with:items', 'string'],
            'items.*.unit_price' => ['required_with:items', 'string'],
            'items.*.discount_amount' => ['nullable', 'string'],
            'items.*.sort_order' => ['nullable', 'integer'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }
}
