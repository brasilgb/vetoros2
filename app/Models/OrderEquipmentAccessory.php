<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderEquipmentAccessoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderEquipmentAccessory extends Model
{
    /** @use HasFactory<OrderEquipmentAccessoryFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_id', 'name', 'quantity', 'notes'];

    protected $attributes = ['quantity' => 1];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderEquipmentAccessory $accessory): void {
            if (! Order::withoutGlobalScopes()->whereKey($accessory->order_id)->where('tenant_id', $accessory->tenant_id)->exists()) {
                throw new LogicException('Accessory order must belong to the current tenant.');
            }
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
