<?php

namespace App\Http\Requests\Budgets;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachBudgetToOrderRequest extends FormRequest
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
            'order_id' => ['required', 'integer', Rule::exists('orders', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
        ];
    }
}
