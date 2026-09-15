<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderAssignmentHistoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderAssignmentHistory extends Model
{
    /** @property int $tenant_id */
    /** @property int $order_id */
    /** @property int|null $from_user_id */
    /** @property int|null $to_user_id */
    /** @property int|null $changed_by */
    /** @use HasFactory<OrderAssignmentHistoryFactory> */
    use BelongsToTenant, HasFactory;

    protected $table = 'order_assignment_history';

    public $timestamps = false;

    protected $fillable = ['order_id', 'from_user_id', 'to_user_id', 'changed_by', 'changed_at', 'note', 'created_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderAssignmentHistory $history): void {
            $order = Order::withoutGlobalScopes()->find($history->order_id);
            if (! $order || (int) $order->tenant_id !== (int) $history->tenant_id) {
                throw new LogicException('Assignment history must belong to an order in the current tenant.');
            }

            foreach (['from_user_id', 'to_user_id', 'changed_by'] as $key) {
                if ($history->{$key} !== null && ! User::withoutGlobalScopes()->whereKey($history->{$key})
                    ->where('tenant_id', $history->tenant_id)->exists()) {
                    throw new LogicException('Assignment history users must belong to the current tenant.');
                }
            }
        });
        static::updating(fn (): never => throw new LogicException('Assignment history is immutable.'));
        static::deleting(fn (): never => throw new LogicException('Assignment history is immutable.'));
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
