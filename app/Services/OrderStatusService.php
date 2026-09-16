<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class OrderStatusService
{
    private static int $transitionDepth = 0;

    /** @var array<string, list<OrderStatus>> */
    private const TRANSITIONS = [
        'open' => [OrderStatus::BUDGET_GENERATED, OrderStatus::IN_PROGRESS, OrderStatus::WAITING_CUSTOMER, OrderStatus::NOT_EXECUTED, OrderStatus::CANCELLED],
        'budget_generated' => [OrderStatus::BUDGET_APPROVED, OrderStatus::BUDGET_REJECTED, OrderStatus::WAITING_CUSTOMER, OrderStatus::CANCELLED],
        'budget_approved' => [OrderStatus::IN_PROGRESS, OrderStatus::CANCELLED],
        'budget_rejected' => [OrderStatus::OPEN, OrderStatus::NOT_EXECUTED, OrderStatus::CANCELLED],
        'waiting_customer' => [OrderStatus::OPEN, OrderStatus::BUDGET_GENERATED, OrderStatus::IN_PROGRESS, OrderStatus::NOT_EXECUTED, OrderStatus::CANCELLED],
        'in_progress' => [OrderStatus::WAITING_CUSTOMER, OrderStatus::COMPLETED, OrderStatus::NOT_EXECUTED, OrderStatus::CANCELLED],
        'completed' => [OrderStatus::DELIVERED],
        'not_executed' => [OrderStatus::DELIVERED],
        'delivered' => [],
        'cancelled' => [],
    ];

    public function transition(Order $order, OrderStatus $to, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $to, $actor, $note): Order {
            if ($actor !== null && (int) $actor->tenant_id !== (int) $order->tenant_id) {
                throw new LogicException('The status actor must belong to the order tenant.');
            }

            if ($actor !== null && ! User::withoutGlobalScopes()
                ->whereKey($actor->id)
                ->where('tenant_id', $order->tenant_id)
                ->exists()) {
                throw new LogicException('The status actor must belong to the order tenant.');
            }

            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $from = $lockedOrder->status;

            if (! in_array($to, self::TRANSITIONS[$from->value], true)) {
                throw new LogicException("Cannot transition order from {$from->value} to {$to->value}.");
            }

            if ($to === OrderStatus::CANCELLED && trim((string) $note) === '') {
                throw new LogicException('Cancellation requires a note.');
            }

            $now = now();
            $updates = ['status' => $to];

            match ($to) {
                OrderStatus::IN_PROGRESS => $updates['started_at'] = $lockedOrder->started_at ?? $now,
                OrderStatus::COMPLETED => $updates['completed_at'] = $lockedOrder->completed_at ?? $now,
                OrderStatus::DELIVERED => $updates['delivered_at'] = $lockedOrder->delivered_at ?? $now,
                OrderStatus::CANCELLED => $updates['cancelled_at'] = $lockedOrder->cancelled_at ?? $now,
                default => null,
            };

            self::$transitionDepth++;
            try {
                $lockedOrder->fill($updates)->save();
            } finally {
                self::$transitionDepth--;
            }

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'changed_by' => $actor?->id,
                'changed_at' => $now,
                'note' => $note,
            ]);

            return $lockedOrder->fresh();
        });
    }

    public static function isTransitioning(): bool
    {
        return self::$transitionDepth > 0;
    }
}
