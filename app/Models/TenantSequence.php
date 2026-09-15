<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TenantSequence extends Model
{
    use BelongsToTenant;

    protected $fillable = ['key', 'current_value'];

    protected $casts = ['current_value' => 'integer'];
}
