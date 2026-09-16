<?php

namespace Database\Factories;

use App\Models\BudgetTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BudgetTemplate> */
class BudgetTemplateFactory extends Factory
{
    protected $model = BudgetTemplate::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['name' => fake()->sentence(3), 'description' => fake()->optional()->sentence(), 'active' => true, 'notes' => null];
    }
}
