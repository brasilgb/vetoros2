<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerEquipment>
 */
class CustomerEquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'equipment_type_id' => EquipmentType::factory(),
            'equipment_number' => fake()->unique()->numberBetween(1, 999999),
            'brand' => fake()->company(),
            'model' => fake()->bothify('Model-##'),
            'is_active' => true,
        ];
    }
}
