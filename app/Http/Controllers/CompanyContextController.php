<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Support\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyContextController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, CurrentCompany $currentCompany): RedirectResponse
    {
        $company = Company::query()
            ->whereKey($request->integer('company_id'))
            ->where('is_active', true)
            ->first();

        abort_if(! $company, 404);

        abort_unless(
            $request->user()->companies()->whereKey($company->getKey())->exists(),
            403,
        );

        $currentCompany->set($request->user(), $company);

        return back();
    }
}
