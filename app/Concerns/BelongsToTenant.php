<?php

namespace App\Concerns;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $model->ensureTenantMatchesCurrent();
        });

        static::updating(function ($model): void {
            $model->ensureTenantMatchesCurrent();
        });
    }

    private function ensureTenantMatchesCurrent(): void
    {
        $model = $this;
        $tenant = Tenant::current();

        if (! $tenant) {
            throw new LogicException(
                'Cannot create a tenant-owned model without a current tenant.'
            );
        }

        if (! $model->tenant_id) {
            $model->tenant_id = $tenant->getKey();
        }

        if ((int) $model->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException(
                'The model tenant_id does not match the current tenant.'
            );
        }
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
