<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\BudgetItemType;
use Database\Factories\BudgetItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class BudgetItem extends Model
{
    /** @use HasFactory<BudgetItemFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['budget_id', 'type', 'description', 'quantity', 'unit_price', 'discount_amount', 'total', 'sort_order', 'source_template_item_id', 'notes'];

    protected function casts(): array
    {
        return ['type' => BudgetItemType::class, 'quantity' => 'decimal:3', 'unit_price' => 'decimal:2', 'discount_amount' => 'decimal:2', 'total' => 'decimal:2', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (BudgetItem $item): void {
            foreach ([['budget_id', Budget::class], ['source_template_item_id', BudgetTemplateItem::class]] as [$key, $class]) {
                if ($item->{$key} !== null && ! $class::withoutGlobalScopes()->whereKey($item->{$key})->where('tenant_id', $item->tenant_id)->exists()) {
                    throw new LogicException('Budget item relationships must belong to the current tenant.');
                }
            }

            self::assertBudgetIsEditable($item);
        });

        static::deleting(function (BudgetItem $item): void {
            self::assertBudgetIsEditable($item);
        });
    }

    private static function assertBudgetIsEditable(BudgetItem $item): void
    {
        $budget = Budget::withoutGlobalScopes()->find($item->budget_id);
        if ($budget && ! $budget->canEdit()) {
            throw new LogicException('Items of a budget that is no longer a draft cannot be changed.');
        }
    }

    /** @return BelongsTo<Budget, $this> */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /** @return BelongsTo<BudgetTemplateItem, $this> */
    public function sourceTemplateItem(): BelongsTo
    {
        return $this->belongsTo(BudgetTemplateItem::class, 'source_template_item_id');
    }
}
