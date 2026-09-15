<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderEquipmentAccessory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderEquipmentAccessory>
 */
class OrderEquipmentAccessoryFactory extends Factory
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
            'name' => fake()->randomElement(['Carregador', 'Fonte', 'Cabo USB', 'Capa']),
            'quantity' => 1,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
