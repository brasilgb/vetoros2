<?php

namespace App\Http\Requests\Orders;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = Tenant::current()?->getKey();

        return ['customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'customer_equipment_id' => ['nullable', 'integer', Rule::exists('customer_equipments', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'equipment_type_id' => ['required', 'integer', Rule::exists('equipment_types', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'company_id' => ['required', 'integer', Rule::exists('companies', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])], 'reported_issue' => ['required', 'string'], 'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))]];
    }
}
