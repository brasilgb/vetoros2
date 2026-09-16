<?php

namespace App\Support;

use App\Models\Budget;

class BudgetPresenter
{
    /** @return array<string, mixed> */
    public static function summary(Budget $budget): array
    {
        return [
            'id' => $budget->id,
            'budget_number' => $budget->budget_number,
            'status' => $budget->status->value,
            'total' => $budget->total,
            'created_at' => $budget->created_at?->toIso8601String(),
            'customer' => $budget->customer ? [
                'id' => $budget->customer->id,
                'name' => $budget->customer->name ?: $budget->customer->trade_name,
            ] : null,
            'company' => $budget->company ? ['id' => $budget->company->id, 'trade_name' => $budget->company->trade_name] : null,
            'branch' => $budget->branch ? ['id' => $budget->branch->id, 'name' => $budget->branch->name] : null,
            'order' => $budget->order ? ['id' => $budget->order->id, 'order_number' => $budget->order->order_number] : null,
            'creator' => $budget->creator ? ['id' => $budget->creator->id, 'name' => $budget->creator->name] : null,
            'items_count' => $budget->items_count ?? $budget->items()->count(),
        ];
    }

    /** @return array<string, mixed> */
    public static function detail(Budget $budget): array
    {
        return [
            ...self::summary($budget),
            'subtotal' => $budget->subtotal,
            'discount_amount' => $budget->discount_amount,
            'valid_until' => $budget->valid_until?->toDateString(),
            'notes' => $budget->notes,
            'order' => $budget->order ? [
                'id' => $budget->order->id,
                'order_number' => $budget->order->order_number,
                'status' => $budget->order->status->value,
            ] : null,
            'customer' => $budget->customer ? [
                'id' => $budget->customer->id,
                'name' => $budget->customer->name ?: $budget->customer->trade_name,
                'document' => $budget->customer->cpf ?: $budget->customer->cnpj,
            ] : null,
            'items' => $budget->items->map(fn ($item): array => [
                'id' => $item->id,
                'type' => $item->type->value,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'total' => $item->total,
                'sort_order' => $item->sort_order,
                'source_template_item_id' => $item->source_template_item_id,
                'notes' => $item->notes,
            ])->all(),
            'can_edit' => $budget->canEdit(),
            'can_send' => $budget->canSend(),
            'can_approve' => $budget->canApprove(),
            'can_reject' => $budget->canReject(),
            'can_cancel' => $budget->canCancel(),
        ];
    }
}
