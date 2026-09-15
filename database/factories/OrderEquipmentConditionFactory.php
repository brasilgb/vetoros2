<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderEquipmentCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderEquipmentCondition>
 */
class OrderEquipmentConditionFactory extends Factory
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
            'description' => fake()->sentence(),
            'severity' => 'minor',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
