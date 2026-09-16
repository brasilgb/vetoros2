<?php

namespace App\Http\Controllers\BudgetTemplates;

use App\Http\Concerns\HandlesDomainActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetTemplates\StoreBudgetTemplateItemRequest;
use App\Http\Requests\BudgetTemplates\UpdateBudgetTemplateItemRequest;
use App\Models\BudgetTemplate;
use App\Services\BudgetCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetTemplateItemController extends Controller
{
    use HandlesDomainActions;

    public function store(StoreBudgetTemplateItemRequest $request, int $budgetTemplate): RedirectResponse
    {
        $template = BudgetTemplate::query()->findOrFail($budgetTemplate);
        $data = $request->validated();

        return $this->attempt(function () use ($template, $data): void {
            $calculated = BudgetCalculator::item($data['quantity'], $data['unit_price'], $data['discount_amount'] ?? '0.00');
            $template->items()->create([
                ...$data,
                ...$calculated,
                'sort_order' => $data['sort_order'] ?? ($template->items()->max('sort_order') + 1),
            ]);
        }, 'Item adicionado ao orçamento pré-definido.');
    }

    public function update(UpdateBudgetTemplateItemRequest $request, int $budgetTemplate, int $item): RedirectResponse
    {
        $template = BudgetTemplate::query()->findOrFail($budgetTemplate);
        $templateItem = $template->items()->findOrFail($item);
        $data = $request->validated();

        return $this->attempt(function () use ($templateItem, $data): void {
            $calculated = BudgetCalculator::item($data['quantity'], $data['unit_price'], $data['discount_amount'] ?? '0.00');
            $templateItem->update([...$data, ...$calculated]);
        }, 'Item atualizado.');
    }

    public function destroy(Request $request, int $budgetTemplate, int $item): RedirectResponse
    {
        $template = BudgetTemplate::query()->findOrFail($budgetTemplate);
        $templateItem = $template->items()->findOrFail($item);

        return $this->attempt(function () use ($templateItem): void {
            $templateItem->delete();
        }, 'Item removido.');
    }
}
