<?php

use App\Http\Controllers\Orders\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.tenant', 'current.company'])->group(function (): void {
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
});
