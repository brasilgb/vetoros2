<?php

namespace Database\Factories;

use App\Enums\CompanyType;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => CompanyType::HEADQUARTERS->value,
            'legal_name' => fake()->company(),
            'trade_name' => fake()->company(),
            'cnpj' => fake()->unique()->numerify('##############'),
            'is_active' => true,
        ];
    }

    public function headquarters(): static
    {
        return $this->state([
            'type' => CompanyType::HEADQUARTERS->value,
            'parent_id' => null,
        ]);
    }

    public function branch(Company|int|null $headquarters = null): static
    {
        $state = [
            'type' => CompanyType::BRANCH->value,
        ];

        if ($headquarters !== null) {
            $state['parent_id'] = $headquarters instanceof Company
                ? $headquarters->getKey()
                : $headquarters;
        }

        return $this->state($state);
    }
}
