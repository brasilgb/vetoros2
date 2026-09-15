<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\ChecklistTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class ChecklistTemplate extends Model
{
    /** @use HasFactory<ChecklistTemplateFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['equipment_type_id', 'name', 'type', 'description', 'is_active'];

    protected $attributes = ['type' => 'entry', 'is_active' => true];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (ChecklistTemplate $template): void {
            if ($template->equipment_type_id !== null && ! EquipmentType::withoutGlobalScopes()->whereKey($template->equipment_type_id)->where('tenant_id', $template->tenant_id)->exists()) {
                throw new LogicException('Checklist template equipment type must belong to the current tenant.');
            }
        });
    }

    /** @return BelongsTo<EquipmentType, $this> */
    public function equipmentType(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class);
    }

    /** @return HasMany<ChecklistTemplateItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ChecklistTemplateItem::class)->orderBy('sort_order');
    }
}
