<?php

namespace App\Responses;

use App\Models\Tenant;
use App\Support\CurrentCompany;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user?->isRootAdmin()) {
            app(CurrentCompany::class)->forget();
            Tenant::forgetCurrent();

            return redirect()->route('admin.dashboard');
        }

        $tenant = $user?->tenant_id
            ? Tenant::query()
                ->whereKey($user->tenant_id)
                ->where('active', true)
                ->first()
            : null;

        if ($tenant) {
            $tenant->makeCurrent();

            try {
                app(CurrentCompany::class)->forget();
                $company = app(CurrentCompany::class)->get($user);

                if ($company) {
                    app(CurrentCompany::class)->set($user, $company);
                }
            } finally {
                Tenant::forgetCurrent();
            }
        }

        return redirect()->intended(config('fortify.home'));
    }
}
