<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use LogicException;

class CurrentCompany
{
    public const SESSION_KEY = 'current_company_id';

    public function get(User $user): ?Company
    {
        if (! Tenant::current()) {
            throw new LogicException('Cannot resolve a company without a current tenant.');
        }

        $companyId = session(self::SESSION_KEY);

        if ($companyId) {
            return $user->companies()
                ->whereKey($companyId)
                ->where('companies.is_active', true)
                ->first();
        }

        return $user->defaultCompany()
            ?? $user->companies()->where('companies.is_active', true)->first();
    }

    public function set(User $user, Company $company): void
    {
        if (! Tenant::current()) {
            throw new LogicException('Cannot set a company without a current tenant.');
        }

        $allowed = $user->companies()
            ->whereKey($company->getKey())
            ->where('companies.is_active', true)
            ->exists();

        if (! $allowed) {
            throw new LogicException('User does not have access to this company.');
        }

        session([self::SESSION_KEY => $company->getKey()]);
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
