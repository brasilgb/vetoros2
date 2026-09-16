<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Concerns\HandlesDomainActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budgets\StoreBudgetItemRequest;
use App\Http\Requests\Budgets\UpdateBudgetItemRequest;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Services\BudgetCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetItemController extends Controller
{
    use HandlesDomainActions;

    public function store(StoreBudgetItemRequest $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $data = $request->validated();

        return $this->attempt(function () use ($model, $data): void {
            $calculated = BudgetCalculator::item($data['quantity'], $data['unit_price'], $data['discount_amount'] ?? '0.00');
            $model->items()->create([
                ...$data,
                ...$calculated,
                'sort_order' => $data['sort_order'] ?? ($model->items()->max('sort_order') + 1),
            ]);
            $this->recalculateTotals($model);
        }, 'Item adicionado.');
    }

    public function update(UpdateBudgetItemRequest $request, int $budget, int $item): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $budgetItem = $model->items()->findOrFail($item);
        $data = $request->validated();

        return $this->attempt(function () use ($model, $budgetItem, $data): void {
            $calculated = BudgetCalculator::item($data['quantity'], $data['unit_price'], $data['discount_amount'] ?? '0.00');
            $budgetItem->update([...$data, ...$calculated]);
            $this->recalculateTotals($model);
        }, 'Item atualizado.');
    }

    public function destroy(Request $request, int $budget, int $item): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $budgetItem = $model->items()->findOrFail($item);

        return $this->attempt(function () use ($model, $budgetItem): void {
            $budgetItem->delete();
            $this->recalculateTotals($model);
        }, 'Item removido.');
    }

    private function recalculateTotals(Budget $budget): void
    {
        $items = $budget->items()->get(['total'])->map(fn (BudgetItem $item): array => ['total' => $item->total])->all();
        $budget->update(BudgetCalculator::budget($items, $budget->discount_amount));
    }
}
