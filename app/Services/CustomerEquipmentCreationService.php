<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class CustomerEquipmentCreationService
{
    /** @param array<string, mixed> $attributes */
    public function create(Customer $customer, EquipmentType $type, User $actor, array $attributes = []): CustomerEquipment
    {
        $tenant = Tenant::current();
        if (! $tenant || (int) $actor->tenant_id !== (int) $tenant->getKey() || (int) $customer->tenant_id !== (int) $tenant->getKey() || (int) $type->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('Customer, equipment type and actor must belong to the current tenant.');
        }

        return DB::transaction(fn (): CustomerEquipment => CustomerEquipment::create([...$attributes, 'tenant_id' => $tenant->getKey(), 'customer_id' => $customer->getKey(), 'equipment_type_id' => $type->getKey(), 'equipment_number' => app(TenantSequenceService::class)->next('customer_equipments')])->fresh(['customer', 'equipmentType']));
    }
}
