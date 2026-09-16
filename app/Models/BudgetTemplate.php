<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\BudgetTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetTemplate extends Model
{
    /** @use HasFactory<BudgetTemplateFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['name', 'description', 'active', 'notes'];

    protected $attributes = ['active' => true];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<BudgetTemplateItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetTemplateItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
