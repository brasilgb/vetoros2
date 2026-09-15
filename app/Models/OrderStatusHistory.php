<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderStatusHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderStatusHistory extends Model
{
    /** @property int $tenant_id */
    /** @property int $order_id */
    /** @property int|null $changed_by */
    /** @use HasFactory<OrderStatusHistoryFactory> */
    use BelongsToTenant, HasFactory;

    protected $table = 'order_status_history';

    public $timestamps = false;

    protected $fillable = ['order_id', 'from_status', 'to_status', 'changed_by', 'changed_at', 'note', 'created_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderStatusHistory $history): void {
            if (! Order::withoutGlobalScopes()->whereKey($history->order_id)->where('tenant_id', $history->tenant_id)->exists()) {
                throw new LogicException('Order status history must belong to the current tenant order.');
            }

            if ($history->changed_by !== null && ! User::withoutGlobalScopes()
                ->whereKey($history->changed_by)
                ->where('tenant_id', $history->tenant_id)
                ->exists()) {
                throw new LogicException('The status history user must belong to the current tenant.');
            }
        });

        static::updating(function (): void {
            throw new LogicException('Order status history is immutable.');
        });

        static::deleting(function (): void {
            throw new LogicException('Order status history is immutable.');
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
