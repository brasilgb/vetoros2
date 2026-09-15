<?php

namespace App\Models;

use Database\Factories\ChecklistTemplateItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistTemplateItem extends Model
{
    /** @use HasFactory<ChecklistTemplateItemFactory> */
    use HasFactory;

    protected $fillable = ['checklist_template_id', 'description', 'input_type', 'is_required', 'sort_order'];

    protected $attributes = ['input_type' => 'boolean', 'is_required' => false, 'sort_order' => 0];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return BelongsTo<ChecklistTemplate, $this> */
    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }
}
