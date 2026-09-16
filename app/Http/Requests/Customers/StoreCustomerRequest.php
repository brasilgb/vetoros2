<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['individual', 'company'])], 'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'], 'trade_name' => ['nullable', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:20'], 'cnpj' => ['nullable', 'string', 'max:20'], 'birth_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:30'], 'mobile' => ['nullable', 'string', 'max:30'], 'whatsapp' => ['nullable', 'string', 'max:30'],
            'contact_name' => ['nullable', 'string', 'max:255'], 'contact_phone' => ['nullable', 'string', 'max:30'], 'contact_email' => ['nullable', 'email', 'max:255'], 'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'zip_code' => ['nullable', 'string', 'max:12'], 'state' => ['nullable', 'string', 'size:2'], 'city' => ['nullable', 'string', 'max:255'], 'district' => ['nullable', 'string', 'max:255'], 'street' => ['nullable', 'string', 'max:255'], 'number' => ['nullable', 'string', 'max:50'], 'complement' => ['nullable', 'string', 'max:255'], 'observations' => ['nullable', 'string'],
        ];
    }
}
