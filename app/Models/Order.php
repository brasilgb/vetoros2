<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Services\OrderStatusService;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class Order extends Model
{
    /** @property int $tenant_id */
    /** @property int $company_id */
    /** @property int $branch_id */
    /** @property int $order_number */
    /** @property OrderStatus $status */
    /** @use HasFactory<OrderFactory> */
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'order_number', 'customer_id', 'customer_equipment_id',
        'equipment_type_id', 'status', 'priority', 'reported_issue',
        'technical_diagnosis', 'solution', 'received_at', 'started_at',
        'completed_at', 'delivered_at', 'cancelled_at', 'created_by', 'assigned_to',
    ];

    protected $attributes = ['status' => 'open', 'priority' => 'normal'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'priority' => OrderPriority::class,
            'received_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if ($order->exists && $order->isDirty('status') && ! OrderStatusService::isTransitioning()) {
                throw new LogicException('Order status must be changed through OrderStatusService.');
            }

            foreach ([
                ['company_id', Company::class], ['customer_id', Customer::class],
                ['customer_equipment_id', CustomerEquipment::class], ['equipment_type_id', EquipmentType::class],
                ['created_by', User::class], ['assigned_to', User::class],
            ] as [$key, $class]) {
                if ($order->{$key} !== null && ! $class::withoutGlobalScopes()->whereKey($order->{$key})->where('tenant_id', $order->tenant_id)->exists()) {
                    throw new LogicException('Order relationships must belong to the current tenant.');
                }
            }

            if ($order->branch_id === null || ! Branch::withoutGlobalScopes()
                ->whereKey($order->branch_id)
                ->where('tenant_id', $order->tenant_id)
                ->where('company_id', $order->company_id)
                ->exists()) {
                throw new LogicException('Order must belong to a branch of its company and tenant.');
            }

            if ($order->customer_equipment_id !== null && ! CustomerEquipment::withoutGlobalScopes()
                ->whereKey($order->customer_equipment_id)
                ->where('tenant_id', $order->tenant_id)
                ->where('customer_id', $order->customer_id)
                ->where('equipment_type_id', $order->equipment_type_id)
                ->exists()) {
                throw new LogicException('The customer equipment must belong to the order customer and equipment type.');
            }
        });
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

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<CustomerEquipment, $this> */
    public function customerEquipment(): BelongsTo
    {
        return $this->belongsTo(CustomerEquipment::class);
    }

    /** @return BelongsTo<EquipmentType, $this> */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<OrderStatusHistory, $this> */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('changed_at')->orderBy('id');
    }

    /** @return HasMany<OrderAssignmentHistory, $this> */
    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(OrderAssignmentHistory::class)->orderBy('changed_at')->orderBy('id');
    }

    /** @return HasOne<OrderSnapshot, $this> */
    public function snapshot(): HasOne
    {
        return $this->hasOne(OrderSnapshot::class);
    }

    /** @return HasMany<OrderEquipmentAccessory, $this> */
    public function equipmentAccessories(): HasMany
    {
        return $this->hasMany(OrderEquipmentAccessory::class);
    }

    /** @return HasMany<OrderEquipmentCondition, $this> */
    public function equipmentConditions(): HasMany
    {
        return $this->hasMany(OrderEquipmentCondition::class);
    }

    /** @return HasMany<OrderChecklist, $this> */
    public function checklists(): HasMany
    {
        return $this->hasMany(OrderChecklist::class);
    }

    /** @return HasMany<OrderMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(OrderMedia::class);
    }
}
