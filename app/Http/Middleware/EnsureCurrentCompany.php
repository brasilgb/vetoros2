<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(Tenant::current() !== null, 403);

        $user = $request->user();

        abort_if(! $user, 401);

        if ($user->isRootAdmin()) {
            return $next($request);
        }

        $company = app(CurrentCompany::class)->get($user);

        abort_if(! $company, 403);

        app()->instance('currentCompany', $company);

        return $next($request);
    }
}
