<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Services\OrderVisibilityService;
use App\Services\TenantSequenceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Customer::query()->withCount('customerEquipments');
        if ($search = trim($request->string('search')->toString())) {
            $query->where(function (Builder $q) use ($search): void {
                foreach (['customer_number', 'name', 'trade_name', 'cpf', 'cnpj', 'phone', 'mobile', 'whatsapp', 'email'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
        if ($request->boolean('has_equipment')) {
            $query->has('customerEquipments');
        }

        return Inertia::render('customers/index', ['customers' => $query->orderBy('name')->paginate(20)->withQueryString(), 'filters' => $request->only(['search', 'type', 'active', 'has_equipment'])]);
    }

    public function create(): Response
    {
        return Inertia::render('customers/create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([...$request->validated(), 'customer_number' => app(TenantSequenceService::class)->next('customers')]);

        return to_route('customers.show', $customer)->with('success', 'Cliente criado.');
    }

    public function show(Request $request, Customer $customer): Response
    {
        $customer->load(['customerEquipments.equipmentType']);
        $orders = app(OrderVisibilityService::class)->visibleQuery($request->user())->where('customer_id', $customer->id)->with(['branch', 'customerEquipment', 'assignee', 'latestBudget'])->latest('id')->paginate(10)->withQueryString();
        $orders->through(fn ($order): array => ['id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status->value, 'branch' => $order->branch?->name, 'equipment' => $order->customerEquipment?->model ?: $order->customerEquipment?->brand, 'technician' => $order->assignee?->name, 'created_at' => $order->created_at?->toIso8601String(), 'budget' => $order->latestBudget?->budget_number]);

        return Inertia::render('customers/show', ['customer' => $customer, 'equipmentTypes' => EquipmentType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']), 'orders' => $orders]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('customers/edit', ['customer' => $customer]);
    }

    public function update(StoreCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.show', $customer)->with('success', 'Cliente atualizado.');
    }
}
