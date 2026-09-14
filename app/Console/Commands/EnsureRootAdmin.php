<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

#[Signature('root-admin:ensure {--name=} {--email=}')]
#[Description('Create or update the global root administrator.')]
class EnsureRootAdmin extends Command
{
    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Name')));
        $email = trim((string) ($this->option('email') ?: $this->ask('Email')));

        $validator = Validator::make(
            compact('name', 'email'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ],
        );

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user && $this->hasTenantAssociations($user)) {
            $this->components->error(
                'The user already belongs to a tenant or has company access. Create a separate global user.',
            );

            return self::FAILURE;
        }

        if ($user && ! $user->isRootAdmin() && ! $this->confirm(
            "Promote [{$user->email}] to rootAdmin?",
            false,
        )) {
            $this->components->warn('No changes were made.');

            return self::SUCCESS;
        }

        $password = (string) $this->secret(
            $user ? 'Password (leave empty to keep current)' : 'Password',
        );

        if (! $user && $password === '') {
            $this->components->error('A password is required for a new rootAdmin.');

            return self::FAILURE;
        }

        if ($password !== '' && ! $this->passwordIsValid($password)) {
            return self::FAILURE;
        }

        DB::transaction(function () use ($email, $name, $password, $user): void {
            $attributes = [
                'name' => $name,
                'email' => $email,
                'tenant_id' => null,
                'is_root_admin' => true,
            ];

            if ($password !== '') {
                $attributes['password'] = Hash::make($password);
            }

            if ($user) {
                $user->forceFill($attributes)->save();

                return;
            }

            User::forceCreate($attributes);
        });

        $this->components->info($user ? 'rootAdmin updated successfully.' : 'rootAdmin created successfully.');

        return self::SUCCESS;
    }

    private function hasTenantAssociations(User $user): bool
    {
        return $user->tenant_id !== null
            || DB::table('company_user')->where('user_id', $user->getKey())->exists();
    }

    private function passwordIsValid(string $password): bool
    {
        $rules = ['required', 'string'];

        if (app()->isProduction()) {
            $rules[] = Password::defaults();
        }

        try {
            Validator::make(['password' => $password], ['password' => $rules])->validate();
        } catch (ValidationException $exception) {
            $this->components->error($exception->validator->errors()->first('password'));

            return false;
        }

        return true;
    }
}
