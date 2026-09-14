<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(! $user, 401);

        if ($user->isRootAdmin()) {
            return $next($request);
        }

        abort_if(! $user->tenant_id, 403);

        $tenant = Tenant::query()
            ->whereKey($user->tenant_id)
            ->where('active', true)
            ->first();

        abort_if(! $tenant, 403);

        $tenant->makeCurrent();

        try {
            return $next($request);
        } finally {
            Tenant::forgetCurrent();
        }
    }
}
