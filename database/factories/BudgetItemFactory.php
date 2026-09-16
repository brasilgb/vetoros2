<?php

namespace Database\Factories;

use App\Enums\BudgetItemType;
use App\Models\Budget;
use App\Models\BudgetItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BudgetItem> */
class BudgetItemFactory extends Factory
{
    protected $model = BudgetItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['budget_id' => Budget::factory(), 'type' => BudgetItemType::SERVICE, 'description' => fake()->sentence(), 'quantity' => '1.000', 'unit_price' => '100.00', 'discount_amount' => '0.00', 'total' => '100.00', 'sort_order' => 0, 'source_template_item_id' => null, 'notes' => null];
    }
}
