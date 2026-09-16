<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\BudgetStatus;
use App\Services\BudgetApprovalService;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_id', 'customer_id', 'company_id', 'branch_id', 'budget_number', 'status', 'valid_until', 'subtotal', 'discount_amount', 'total', 'notes', 'created_by'];

    protected $attributes = ['status' => BudgetStatus::DRAFT, 'discount_amount' => '0.00', 'subtotal' => '0.00', 'total' => '0.00'];

    protected function casts(): array
    {
        return ['status' => BudgetStatus::class, 'valid_until' => 'date', 'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2', 'budget_number' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Budget $budget): void {
            if ($budget->exists && $budget->isDirty('status') && ! BudgetApprovalService::isTransitioning()) {
                throw new LogicException('Budget status must be changed through BudgetApprovalService.');
            }

            if ($budget->order_id !== null) {
                $order = Order::withoutGlobalScopes()->whereKey($budget->order_id)->where('tenant_id', $budget->tenant_id)->first();
                if (! $order) {
                    throw new LogicException('Budget order must belong to the current tenant.');
                }

                $budget->customer_id ??= $order->customer_id;
                $budget->company_id ??= $order->company_id;
                $budget->branch_id ??= $order->branch_id;

                if ((int) $budget->customer_id !== (int) $order->customer_id) {
                    throw new LogicException('Budget customer must match the order customer.');
                }
                if ((int) $budget->company_id !== (int) $order->company_id) {
                    throw new LogicException('Budget company must match the order company.');
                }
                if ($order->branch_id !== null && (int) $budget->branch_id !== (int) $order->branch_id) {
                    throw new LogicException('Budget branch must match the order branch.');
                }
            }

            foreach ([['customer_id', Customer::class], ['company_id', Company::class], ['created_by', User::class]] as [$key, $class]) {
                if ($budget->{$key} !== null && ! $class::withoutGlobalScopes()->whereKey($budget->{$key})->where('tenant_id', $budget->tenant_id)->exists()) {
                    throw new LogicException('Budget relationships must belong to the current tenant.');
                }
            }

            if ($budget->branch_id !== null && ! Branch::withoutGlobalScopes()
                ->whereKey($budget->branch_id)
                ->where('tenant_id', $budget->tenant_id)
                ->where('company_id', $budget->company_id)
                ->exists()) {
                throw new LogicException('Budget branch must belong to its company and tenant.');
            }
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return HasMany<BudgetItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canEdit(): bool
    {
        return $this->status === BudgetStatus::DRAFT;
    }

    public function canSend(): bool
    {
        return $this->status === BudgetStatus::DRAFT;
    }

    public function canApprove(): bool
    {
        return $this->status === BudgetStatus::SENT;
    }

    public function canReject(): bool
    {
        return $this->status === BudgetStatus::SENT;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [BudgetStatus::DRAFT, BudgetStatus::SENT], true);
    }
}
