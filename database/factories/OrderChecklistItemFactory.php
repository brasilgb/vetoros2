<?php

namespace Database\Factories;

use App\Models\OrderChecklist;
use App\Models\OrderChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderChecklistItem>
 */
class OrderChecklistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_checklist_id' => OrderChecklist::factory(),
            'label' => fake()->sentence(),
            'input_type' => 'boolean',
            'is_required' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
