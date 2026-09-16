<?php

use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Customers\CustomerEquipmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.tenant', 'current.company'])->group(function (): void {
    Route::resource('customers', CustomerController::class)->except(['destroy']);
    Route::post('customers/{customer}/equipment', [CustomerEquipmentController::class, 'store'])->name('customers.equipment.store');
});
