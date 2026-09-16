<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Concerns\HandlesDomainActions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budgets\AttachBudgetToOrderRequest;
use App\Models\Budget;
use App\Models\Order;
use App\Services\BudgetCreationService;
use App\Services\OrderBudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BudgetWorkflowController extends Controller
{
    use HandlesDomainActions;

    public function send(Request $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $actor = $request->user();

        return $this->attempt(fn () => app(OrderBudgetService::class)->send($model, $actor), 'Orçamento enviado.');
    }

    public function approve(Request $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $actor = $request->user();

        return $this->attempt(fn () => app(OrderBudgetService::class)->approve($model, $actor), 'Orçamento aprovado.');
    }

    public function reject(Request $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $actor = $request->user();

        return $this->attempt(fn () => app(OrderBudgetService::class)->reject($model, $actor), 'Orçamento rejeitado.');
    }

    public function cancel(Request $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $actor = $request->user();

        return $this->attempt(fn () => app(OrderBudgetService::class)->cancel($model, $actor), 'Orçamento cancelado.');
    }

    public function reopen(Request $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $actor = $request->user();

        abort_if($model->order_id === null, 422);
        $order = Order::query()->findOrFail($model->order_id);

        return $this->attempt(fn () => app(OrderBudgetService::class)->reopenAfterRejection($model, $order, $actor), 'Ordem de serviço reaberta.');
    }

    public function attachToOrder(AttachBudgetToOrderRequest $request, int $budget): RedirectResponse
    {
        $model = Budget::query()->findOrFail($budget);
        $order = Order::query()->findOrFail((int) $request->validated('order_id'));
        $actor = $request->user();

        return $this->attempt(fn () => app(BudgetCreationService::class)->attachToOrder($model, $order, $actor), 'Orçamento vinculado à Ordem de Serviço.');
    }
}
