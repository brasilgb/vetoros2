<?php

namespace Database\Factories;

use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentType>
 */
class EquipmentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipment_type_number' => fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->word(),
            'uses_chart' => false,
            'is_active' => true,
        ];
    }
}
