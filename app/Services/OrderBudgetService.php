<?php

namespace App\Services;

use App\Enums\BudgetStatus;
use App\Enums\OrderStatus;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

/** Coordinates the budget workflow without duplicating domain transition rules. */
class OrderBudgetService
{
    /** @param array<int, array<string, mixed>> $items */
    public function createForOrder(Order $order, User $actor, array $items = [], ?BudgetTemplate $template = null, string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        $creator = app(BudgetCreationService::class);

        return $template === null
            ? $creator->create($order, $actor, $items, $discountAmount, $validUntil, $notes)
            : $creator->createFromTemplate($order, $template, $actor, $discountAmount, $validUntil, $notes);
    }

    public function createForOrderFromTemplate(Order $order, BudgetTemplate $template, User $actor, string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        return $this->createForOrder($order, $actor, [], $template, $discountAmount, $validUntil, $notes);
    }

    public function send(Budget $budget, User $actor): Budget
    {
        return DB::transaction(function () use ($budget, $actor): Budget {
            $sent = app(BudgetApprovalService::class)->send($budget, $actor);

            if ($sent->order_id !== null) {
                $order = Order::query()->findOrFail($sent->order_id);
                app(OrderStatusService::class)->transition($order, OrderStatus::BUDGET_GENERATED, $actor);
            }

            return $sent->fresh();
        });
    }

    public function approve(Budget $budget, User $actor): Budget
    {
        return app(BudgetApprovalService::class)->approve($budget, $actor);
    }

    public function reject(Budget $budget, User $actor): Budget
    {
        return app(BudgetApprovalService::class)->reject($budget, $actor);
    }

    public function cancel(Budget $budget, User $actor): Budget
    {
        return app(BudgetApprovalService::class)->cancel($budget, $actor);
    }

    public function reopenAfterRejection(Budget $rejectedBudget, Order $order, User $actor): Order
    {
        if ((int) $rejectedBudget->tenant_id !== (int) $order->tenant_id) {
            throw new LogicException('The rejected budget and the order must belong to the same tenant.');
        }

        if ($rejectedBudget->order_id === null || (int) $rejectedBudget->order_id !== (int) $order->getKey()) {
            throw new LogicException('The rejected budget must belong to the order being reopened.');
        }

        if ($rejectedBudget->status !== BudgetStatus::REJECTED) {
            throw new LogicException('Only a rejected budget can trigger reopening its order.');
        }

        return app(OrderStatusService::class)->transition($order, OrderStatus::OPEN, $actor);
    }
}
