<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderEquipmentConditionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderEquipmentCondition extends Model
{
    /** @use HasFactory<OrderEquipmentConditionFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_id', 'description', 'severity', 'notes'];

    protected static function booted(): void
    {
        static::saving(function (OrderEquipmentCondition $condition): void {
            if (! Order::withoutGlobalScopes()->whereKey($condition->order_id)->where('tenant_id', $condition->tenant_id)->exists()) {
                throw new LogicException('Condition order must belong to the current tenant.');
            }
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
