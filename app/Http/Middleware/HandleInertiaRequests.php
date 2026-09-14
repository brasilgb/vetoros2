<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\CurrentCompany;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = Tenant::current();
        $user = $request->user();
        $currentCompany = $tenant && $user
            ? app(CurrentCompany::class)->get($user)
            : null;

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'tenant' => $tenant?->only(['id', 'name', 'slug']),
            'currentCompany' => $currentCompany?->only([
                'id',
                'trade_name',
                'type',
                'parent_id',
            ]),
            'availableCompanies' => $user && $tenant
                ? $user->companies()
                    ->where('companies.is_active', true)
                    ->orderBy('companies.type')
                    ->orderBy('companies.trade_name')
                    ->get([
                        'companies.id',
                        'companies.trade_name',
                        'companies.type',
                        'companies.parent_id',
                    ])
                : [],
            'permissions' => [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
