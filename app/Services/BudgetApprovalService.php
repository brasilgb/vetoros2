<?php

namespace App\Services;

use App\Enums\BudgetStatus;
use App\Enums\OrderStatus;
use App\Models\Budget;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class BudgetApprovalService
{
    private static int $transitionDepth = 0;

    /** @var array<string, list<BudgetStatus>> */
    private const TRANSITIONS = [
        'draft' => [BudgetStatus::SENT, BudgetStatus::CANCELLED],
        'sent' => [BudgetStatus::APPROVED, BudgetStatus::REJECTED, BudgetStatus::CANCELLED],
        'approved' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public function send(Budget $budget, User $actor): Budget
    {
        return $this->transition($budget, BudgetStatus::SENT, $actor);
    }

    public function approve(Budget $budget, User $actor): Budget
    {
        return $this->transition($budget, BudgetStatus::APPROVED, $actor, OrderStatus::BUDGET_APPROVED);
    }

    public function reject(Budget $budget, User $actor): Budget
    {
        return $this->transition($budget, BudgetStatus::REJECTED, $actor, OrderStatus::BUDGET_REJECTED);
    }

    public function cancel(Budget $budget, User $actor): Budget
    {
        return $this->transition($budget, BudgetStatus::CANCELLED, $actor);
    }

    private function transition(Budget $budget, BudgetStatus $to, User $actor, ?OrderStatus $orderStatus = null): Budget
    {
        return DB::transaction(function () use ($budget, $to, $actor, $orderStatus): Budget {
            if ((int) $actor->tenant_id !== (int) $budget->tenant_id) {
                throw new LogicException('The budget actor must belong to the budget tenant.');
            }

            $locked = Budget::query()->lockForUpdate()->findOrFail($budget->id);
            $from = $locked->status;

            if (! in_array($to, self::TRANSITIONS[$from->value], true)) {
                throw new LogicException("Cannot transition budget from {$from->value} to {$to->value}.");
            }

            self::$transitionDepth++;
            try {
                $locked->fill(['status' => $to])->save();
            } finally {
                self::$transitionDepth--;
            }

            if ($orderStatus !== null && $locked->order_id !== null) {
                $order = Order::query()->findOrFail($locked->order_id);
                app(OrderStatusService::class)->transition($order, $orderStatus, $actor);
            }

            return $locked->fresh();
        });
    }

    public static function isTransitioning(): bool
    {
        return self::$transitionDepth > 0;
    }
}
