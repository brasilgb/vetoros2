<?php

namespace Database\Factories;

use App\Enums\BudgetStatus;
use App\Models\Budget;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Budget> */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['order_id' => Order::factory(), 'budget_number' => fake()->unique()->numberBetween(1, 999999), 'status' => BudgetStatus::DRAFT, 'subtotal' => '100.00', 'discount_amount' => '0.00', 'total' => '100.00', 'created_by' => fn () => User::factory()->create(['tenant_id' => Tenant::current()?->getKey()])];
    }
}
