<?php

namespace App\Http\Requests\Customers;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tenantId = Tenant::current()?->getKey();

        return ['equipment_type_id' => ['required', 'integer', Rule::exists('equipment_types', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId))], 'brand' => ['nullable', 'string', 'max:255'], 'model' => ['nullable', 'string', 'max:255'], 'serial_number' => ['nullable', 'string', 'max:255'], 'imei' => ['nullable', 'string', 'max:255'], 'asset_tag' => ['nullable', 'string', 'max:255'], 'color' => ['nullable', 'string', 'max:100'], 'description' => ['nullable', 'string'], 'observations' => ['nullable', 'string']];
    }
}
