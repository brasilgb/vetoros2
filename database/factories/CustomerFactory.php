<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_number' => fake()->unique()->numberBetween(1, 999999),
            'type' => 'individual',
            'name' => fake()->name(),
            'cpf' => fake()->numerify('###########'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('119########'),
            'is_active' => true,
        ];
    }
}
