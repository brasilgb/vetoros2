<?php

namespace Database\Factories;

use App\Enums\BudgetItemType;
use App\Models\BudgetTemplate;
use App\Models\BudgetTemplateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BudgetTemplateItem> */
class BudgetTemplateItemFactory extends Factory
{
    protected $model = BudgetTemplateItem::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['budget_template_id' => BudgetTemplate::factory(), 'type' => BudgetItemType::SERVICE, 'description' => fake()->sentence(), 'quantity' => '1.000', 'unit_price' => '100.00', 'discount_amount' => '0.00', 'total' => '100.00', 'sort_order' => 0, 'notes' => null];
    }
}
