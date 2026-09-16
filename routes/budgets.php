<?php

use App\Http\Controllers\Budgets\BudgetController;
use App\Http\Controllers\Budgets\BudgetItemController;
use App\Http\Controllers\Budgets\BudgetLookupController;
use App\Http\Controllers\Budgets\BudgetWorkflowController;
use App\Http\Controllers\BudgetTemplates\BudgetTemplateController;
use App\Http\Controllers\BudgetTemplates\BudgetTemplateItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'current.tenant', 'current.company'])->group(function (): void {
    Route::get('budgets/lookup/orders', [BudgetLookupController::class, 'orders'])->name('budgets.lookup.orders');
    Route::get('budgets/lookup/customers', [BudgetLookupController::class, 'customers'])->name('budgets.lookup.customers');

    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('budgets/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::post('budgets', [BudgetController::class, 'store'])->name('budgets.store');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])->name('budgets.show');

    Route::post('budgets/{budget}/items', [BudgetItemController::class, 'store'])->name('budgets.items.store');
    Route::put('budgets/{budget}/items/{item}', [BudgetItemController::class, 'update'])->name('budgets.items.update');
    Route::delete('budgets/{budget}/items/{item}', [BudgetItemController::class, 'destroy'])->name('budgets.items.destroy');

    Route::post('budgets/{budget}/send', [BudgetWorkflowController::class, 'send'])->name('budgets.send');
    Route::post('budgets/{budget}/approve', [BudgetWorkflowController::class, 'approve'])->name('budgets.approve');
    Route::post('budgets/{budget}/reject', [BudgetWorkflowController::class, 'reject'])->name('budgets.reject');
    Route::post('budgets/{budget}/cancel', [BudgetWorkflowController::class, 'cancel'])->name('budgets.cancel');
    Route::post('budgets/{budget}/reopen', [BudgetWorkflowController::class, 'reopen'])->name('budgets.reopen');
    Route::post('budgets/{budget}/attach-to-order', [BudgetWorkflowController::class, 'attachToOrder'])->name('budgets.attach-to-order');

    Route::get('budget-templates', [BudgetTemplateController::class, 'index'])->name('budget-templates.index');
    Route::get('budget-templates/create', [BudgetTemplateController::class, 'create'])->name('budget-templates.create');
    Route::post('budget-templates', [BudgetTemplateController::class, 'store'])->name('budget-templates.store');
    Route::get('budget-templates/{budgetTemplate}', [BudgetTemplateController::class, 'show'])->name('budget-templates.show');
    Route::get('budget-templates/{budgetTemplate}/edit', [BudgetTemplateController::class, 'edit'])->name('budget-templates.edit');
    Route::put('budget-templates/{budgetTemplate}', [BudgetTemplateController::class, 'update'])->name('budget-templates.update');

    Route::post('budget-templates/{budgetTemplate}/items', [BudgetTemplateItemController::class, 'store'])->name('budget-templates.items.store');
    Route::put('budget-templates/{budgetTemplate}/items/{item}', [BudgetTemplateItemController::class, 'update'])->name('budget-templates.items.update');
    Route::delete('budget-templates/{budgetTemplate}/items/{item}', [BudgetTemplateItemController::class, 'destroy'])->name('budget-templates.items.destroy');
});
