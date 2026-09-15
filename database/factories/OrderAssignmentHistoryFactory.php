<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderAssignmentHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderAssignmentHistory>
 */
class OrderAssignmentHistoryFactory extends Factory
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
            'changed_at' => now(),
        ];
    }
}
