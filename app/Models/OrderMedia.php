<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderMedia extends Model
{
    /** @use HasFactory<OrderMediaFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['order_id', 'type', 'path', 'original_name', 'mime_type', 'size', 'description', 'uploaded_by'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderMedia $media): void {
            if (! Order::withoutGlobalScopes()->whereKey($media->order_id)->where('tenant_id', $media->tenant_id)->exists()) {
                throw new LogicException('Media order must belong to the current tenant.');
            }

            if ($media->uploaded_by !== null && ! User::withoutGlobalScopes()->whereKey($media->uploaded_by)->where('tenant_id', $media->tenant_id)->exists()) {
                throw new LogicException('Media uploader must belong to the current tenant.');
            }
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
