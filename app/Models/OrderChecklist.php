<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderChecklistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use LogicException;

class OrderChecklist extends Model
{
    /** @use HasFactory<OrderChecklistFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_id', 'checklist_template_id', 'type', 'name', 'started_at', 'completed_at', 'created_by', 'completed_by'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderChecklist $checklist): void {
            if (! Order::withoutGlobalScopes()->whereKey($checklist->order_id)->where('tenant_id', $checklist->tenant_id)->exists()) {
                throw new LogicException('Checklist order must belong to the current tenant.');
            }

            if ($checklist->checklist_template_id !== null && ! ChecklistTemplate::withoutGlobalScopes()->whereKey($checklist->checklist_template_id)->where('tenant_id', $checklist->tenant_id)->exists()) {
                throw new LogicException('Checklist template must belong to the current tenant.');
            }

            foreach (['created_by', 'completed_by'] as $key) {
                if ($checklist->{$key} !== null && ! User::withoutGlobalScopes()->whereKey($checklist->{$key})->where('tenant_id', $checklist->tenant_id)->exists()) {
                    throw new LogicException('Checklist users must belong to the current tenant.');
                }
            }
        });
    }

    public static function createFromTemplate(Order $order, ChecklistTemplate $template, ?User $creator = null): self
    {
        if ((int) $order->tenant_id !== (int) $template->tenant_id) {
            throw new LogicException('Checklist template must belong to the order tenant.');
        }

        return DB::transaction(function () use ($order, $template, $creator): self {
            $checklist = self::create([
                'order_id' => $order->id,
                'checklist_template_id' => $template->id,
                'type' => $template->type,
                'name' => $template->name,
                'started_at' => now(),
                'created_by' => $creator?->id,
            ]);

            foreach ($template->items()->get() as $item) {
                $checklist->items()->create([
                    'label' => $item->description,
                    'input_type' => $item->input_type,
                    'is_required' => $item->is_required,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $checklist;
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<ChecklistTemplate, $this> */
    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    /** @return HasMany<OrderChecklistItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderChecklistItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
