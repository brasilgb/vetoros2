<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\BudgetItemType;
use Database\Factories\BudgetTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class BudgetTemplateItem extends Model
{
    /** @use HasFactory<BudgetTemplateItemFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['budget_template_id', 'type', 'description', 'quantity', 'unit_price', 'discount_amount', 'total', 'sort_order', 'notes'];

    protected function casts(): array
    {
        return ['type' => BudgetItemType::class, 'quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (BudgetTemplateItem $item): void {
            if (! BudgetTemplate::withoutGlobalScopes()->whereKey($item->budget_template_id)->where('tenant_id', $item->tenant_id)->exists()) {
                throw new LogicException('Budget template item must belong to the current tenant.');
            }
        });
    }

    /** @return BelongsTo<BudgetTemplate, $this> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(BudgetTemplate::class, 'budget_template_id');
    }
}
