<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use App\Models\Order;
use App\Models\OrderChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderChecklist>
 */
class OrderChecklistFactory extends Factory
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
            'checklist_template_id' => ChecklistTemplate::factory(),
            'type' => 'entry',
            'name' => fake()->sentence(3),
            'started_at' => now(),
        ];
    }
}
