<?php

namespace App\Models\Scopes;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use LogicException;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenant = Tenant::current();

        if (! $tenant) {
            throw new LogicException(
                'Tenant scoped query executed without a current tenant.'
            );
        }

        $builder->where(
            $model->qualifyColumn('tenant_id'),
            $tenant->getKey()
        );
    }
}
