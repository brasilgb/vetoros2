<?php

namespace App\Http\Controllers\Orders;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderCreationService;
use App\Services\OrderVisibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = app(OrderVisibilityService::class)->visibleQuery($request->user())->with(['customer', 'branch', 'assignee']);
        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($q) use ($search): void {
                $q->where('order_number', 'like', "%{$search}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }
        $orders = $query->latest('id')->paginate(20)->withQueryString();
        $orders->through(fn (Order $order): array => ['id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status->value, 'priority' => $order->priority->value, 'customer' => $order->customer?->name ?: $order->customer?->trade_name, 'branch' => $order->branch?->name, 'technician' => $order->assignee?->name, 'created_at' => $order->created_at?->toIso8601String()]);

        return Inertia::render('orders/index', ['orders' => $orders, 'filters' => $request->only(['search', 'status']), 'statuses' => array_map(fn (OrderStatus $status): string => $status->value, OrderStatus::cases())]);
    }

    public function create(Request $request): Response
    {
        $customer = $request->integer('customer') ? Customer::query()->findOrFail($request->integer('customer')) : null;
        $equipment = $request->integer('equipment') ? CustomerEquipment::query()->with('equipmentType')->findOrFail($request->integer('equipment')) : null;

        return Inertia::render('orders/create', ['customer' => $customer, 'equipment' => $equipment, 'customers' => Customer::query()->where('is_active', true)->orderBy('name')->limit(100)->get(['id', 'name', 'trade_name']), 'equipmentTypes' => EquipmentType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']), 'companies' => Company::query()->headquarters()->orderBy('trade_name')->get(['id', 'trade_name']), 'branches' => Branch::query()->orderBy('name')->get(['id', 'name', 'company_id']), 'priorities' => array_map(fn (OrderPriority $priority): string => $priority->value, OrderPriority::cases()), 'technicians' => User::query()->where('tenant_id', Tenant::current()?->getKey())->orderBy('name')->get(['id', 'name'])]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $customer = Customer::query()->whereKey($data['customer_id'])->firstOrFail();
        $equipment = ! empty($data['customer_equipment_id']) ? CustomerEquipment::query()->with('equipmentType')->whereKey($data['customer_equipment_id'])->firstOrFail() : null;
        if ($equipment && ((int) $equipment->customer_id !== (int) $customer->id || (int) $equipment->equipment_type_id !== (int) $data['equipment_type_id'])) {
            throw new LogicException('The equipment must belong to the selected customer and equipment type.');
        }
        $order = app(OrderCreationService::class)->create(['order' => $data], $request->user());

        return to_route('orders.show', $order)->with('success', 'Ordem de Serviço criada.');
    }

    public function show(Request $request, Order $order): Response
    {
        abort_unless(app(OrderVisibilityService::class)->canView($request->user(), $order), 404);

        return Inertia::render('orders/show', ['order' => $order->load(['customer', 'customerEquipment.equipmentType', 'branch', 'company', 'assignee', 'latestBudget', 'statusHistory'])]);
    }
}
