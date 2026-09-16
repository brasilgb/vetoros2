<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerEquipmentRequest;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Services\CustomerEquipmentCreationService;
use Illuminate\Http\RedirectResponse;

class CustomerEquipmentController extends Controller
{
    public function store(StoreCustomerEquipmentRequest $request, Customer $customer): RedirectResponse
    {
        $type = EquipmentType::query()->findOrFail($request->integer('equipment_type_id'));
        app(CustomerEquipmentCreationService::class)->create($customer, $type, $request->user(), $request->validated());

        return to_route('customers.show', $customer)->with('success', 'Equipamento cadastrado.');
    }
}
