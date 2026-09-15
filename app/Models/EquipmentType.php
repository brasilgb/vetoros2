<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\EquipmentTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentType extends Model
{
    /** @use HasFactory<EquipmentTypeFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['equipment_type_number', 'name', 'uses_chart', 'is_active'];

    protected $attributes = ['uses_chart' => false, 'is_active' => true];

    protected function casts(): array
    {
        return ['uses_chart' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return HasMany<CustomerEquipment, $this> */
    public function customerEquipments(): HasMany
    {
        return $this->hasMany(CustomerEquipment::class);
    }

    /** @return HasMany<ChecklistTemplate, $this> */
    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(ChecklistTemplate::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
