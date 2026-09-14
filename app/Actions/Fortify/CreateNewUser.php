<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentCompany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'company_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $tenant = Tenant::create([
                'name' => $input['company_name'],
                'slug' => Str::slug($input['company_name']).'-'.Str::lower(Str::random(8)),
                'active' => true,
            ]);

            $tenant->makeCurrent();

            try {
                $user = User::create([
                    'tenant_id' => $tenant->getKey(),
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'password' => $input['password'],
                ]);

                $company = Company::create([
                    'trade_name' => $input['company_name'],
                ]);

                $user->companies()->attach($company, [
                    'tenant_id' => $tenant->getKey(),
                    'is_default' => true,
                ]);

                app(CurrentCompany::class)->set($user, $company);

                return $user;
            } finally {
                Tenant::forgetCurrent();
            }
        });
    }
}
