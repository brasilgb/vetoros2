<?php

use App\Http\Controllers\CompanyContextController;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['current.tenant', 'current.company'])
        ->get('dashboard', fn () => Inertia::render('dashboard'))->name('dashboard');

    Route::post('companies/current', CompanyContextController::class)
        ->middleware('current.tenant')
        ->name('companies.switch');
});

Route::middleware(['auth', 'verified', 'root.admin'])
    ->get('admin', fn () => Inertia::render('admin/dashboard'))
    ->name('admin.dashboard');

Route::middleware([
    'auth',
    'current.tenant',
])->get('/tenant-security-test', function () {
    abort_unless(Tenant::current() !== null, 500);

    return response()->json([
        'ok' => true,
        'tenant_id' => Tenant::current()->id,
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/budgets.php';
require __DIR__.'/customers.php';
require __DIR__.'/orders.php';
