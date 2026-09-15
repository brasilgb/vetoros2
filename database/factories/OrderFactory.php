<?php

namespace Database\Factories;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'order_number' => fake()->unique()->numberBetween(1, 999999),
            'customer_id' => Customer::factory(),
            'customer_equipment_id' => null,
            'equipment_type_id' => EquipmentType::factory(),
            'status' => OrderStatus::OPEN,
            'priority' => OrderPriority::NORMAL,
            'reported_issue' => fake()->sentence(),
            'created_by' => fn () => User::factory()->create(['tenant_id' => Tenant::current()?->getKey()]),
        ];
    }
}
