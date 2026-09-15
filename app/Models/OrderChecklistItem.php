<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderChecklistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderChecklistItem extends Model
{
    /** @use HasFactory<OrderChecklistItemFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_checklist_id', 'label', 'input_type', 'is_required', 'sort_order', 'value_text', 'value_boolean', 'value_number', 'value_date', 'notes'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'value_boolean' => 'boolean', 'value_number' => 'decimal:3', 'value_date' => 'date', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderChecklistItem $item): void {
            if (! OrderChecklist::withoutGlobalScopes()->whereKey($item->order_checklist_id)->where('tenant_id', $item->tenant_id)->exists()) {
                throw new LogicException('Checklist item must belong to the current tenant checklist.');
            }
        });
    }

    /** @return BelongsTo<OrderChecklist, $this> */
    public function orderChecklist(): BelongsTo
    {
        return $this->belongsTo(OrderChecklist::class);
    }
}
