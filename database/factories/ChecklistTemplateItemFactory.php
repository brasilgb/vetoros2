<?php

namespace Database\Factories;

use App\Models\ChecklistTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTemplateItem>
 */
class ChecklistTemplateItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'input_type' => 'boolean',
            'is_required' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
