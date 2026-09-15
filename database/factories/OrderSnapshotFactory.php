<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderSnapshot>
 */
class OrderSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'customer_data' => [],
            'equipment_data' => null,
            'company_data' => [],
            'branch_data' => [],
            'created_at' => now(),
        ];
    }
}
