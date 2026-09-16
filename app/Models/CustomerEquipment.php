<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\CustomerEquipmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class CustomerEquipment extends Model
{
    /** @property int $tenant_id */
    /** @property int $customer_id */
    /** @property int $equipment_type_id */
    /** @use HasFactory<CustomerEquipmentFactory> */
    use BelongsToTenant, HasFactory;

    protected $table = 'customer_equipments';

    protected $fillable = ['customer_id', 'equipment_type_id', 'equipment_number', 'brand', 'model', 'serial_number', 'imei', 'asset_tag', 'color', 'description', 'observations', 'is_active'];

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (CustomerEquipment $equipment): void {
            foreach ([['customer_id', Customer::class], ['equipment_type_id', EquipmentType::class]] as [$key, $class]) {
                if (! $class::withoutGlobalScopes()->whereKey($equipment->{$key})->where('tenant_id', $equipment->tenant_id)->exists()) {
                    throw new LogicException('Customer equipment relationships must belong to the current tenant.');
                }
            }
        });
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return BelongsTo<EquipmentType, $this> */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
