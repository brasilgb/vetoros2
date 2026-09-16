<?php

namespace App\Http\Controllers\Budgets;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\OrderVisibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetLookupController extends Controller
{
    public function orders(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());

        $orders = app(OrderVisibilityService::class)->visibleQuery($request->user())
            ->with('customer:id,name,trade_name')
            ->when($search !== '', fn ($query) => $query->where('order_number', 'like', "%{$search}%"))
            ->latest('id')
            ->limit(20)
            ->get(['id', 'order_number', 'customer_id', 'status'])
            ->map(fn ($order): array => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'customer_id' => $order->customer_id,
                'label' => "#{$order->order_number} — ".($order->customer->name ?: $order->customer->trade_name),
            ]);

        return response()->json($orders);
    }

    public function customers(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());

        $customers = Customer::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('cnpj', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'trade_name', 'cpf', 'cnpj'])
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'label' => $customer->name ?: $customer->trade_name,
            ]);

        return response()->json($customers);
    }
}
