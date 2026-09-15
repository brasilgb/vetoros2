<?php

namespace Database\Factories;

use App\Models\ChecklistTemplate;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistTemplate>
 */
class ChecklistTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipment_type_id' => EquipmentType::factory(),
            'name' => fake()->sentence(3),
            'type' => 'entry',
            'is_active' => true,
        ];
    }
}
