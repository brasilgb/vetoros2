<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use LogicException;

class BudgetVisibilityService
{
    /** @return Builder<Budget> */
    public function visibleQuery(User $user): Builder
    {
        $tenant = Tenant::current();

        if (! $tenant || (int) $user->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The user and current tenant must match.');
        }

        if ($user->isRootAdmin()) {
            return Budget::query();
        }

        $companyIds = DB::table('company_user')
            ->where('tenant_id', $tenant->getKey())
            ->where('user_id', $user->getKey())
            ->pluck('company_id');

        $matrixIds = DB::table('companies')
            ->where('tenant_id', $tenant->getKey())
            ->whereIn('id', $companyIds)
            ->where('type', 'headquarters')
            ->pluck('id');

        $branchIds = DB::table('branch_user')
            ->where('tenant_id', $tenant->getKey())
            ->where('user_id', $user->getKey())
            ->pluck('branch_id');

        return Budget::query()->where(function (Builder $query) use ($matrixIds, $branchIds): void {
            if ($matrixIds->isEmpty() && $branchIds->isEmpty()) {
                $query->whereRaw('1 = 0');

                return;
            }

            if ($matrixIds->isNotEmpty()) {
                $query->whereIn('company_id', $matrixIds);
            }

            if ($branchIds->isNotEmpty()) {
                $method = $matrixIds->isNotEmpty() ? 'orWhereIn' : 'whereIn';
                $query->{$method}('branch_id', $branchIds);
            }
        });
    }

    public function canView(User $user, Budget $budget): bool
    {
        return $this->visibleQuery($user)->whereKey($budget->getKey())->exists();
    }
}
