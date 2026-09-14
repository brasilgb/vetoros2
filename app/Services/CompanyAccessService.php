<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class CompanyAccessService
{
    public function grant(User $user, Company $company, bool $isDefault = false): void
    {
        $tenant = Tenant::current();

        if (! $tenant) {
            throw new LogicException('Cannot grant company access without a current tenant.');
        }

        if ((int) $user->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The user does not belong to the current tenant.');
        }

        if ((int) $company->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The company does not belong to the current tenant.');
        }

        if ($isDefault) {
            DB::table('company_user')
                ->where('tenant_id', $tenant->getKey())
                ->where('user_id', $user->getKey())
                ->update(['is_default' => false]);
        }

        $user->companies()->syncWithoutDetaching([
            $company->getKey() => [
                'tenant_id' => $tenant->getKey(),
                'is_default' => $isDefault,
            ],
        ]);
    }

    public function revoke(User $user, Company $company): void
    {
        $tenant = Tenant::current();

        if (! $tenant) {
            throw new LogicException('Cannot revoke company access without a current tenant.');
        }

        if ((int) $user->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The user does not belong to the current tenant.');
        }

        if ((int) $company->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The company does not belong to the current tenant.');
        }

        $user->companies()->detach($company->getKey());
    }
}
