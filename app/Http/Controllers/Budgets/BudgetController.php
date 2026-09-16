<?php

namespace App\Http\Controllers\Budgets;

use App\Enums\BudgetItemType;
use App\Enums\BudgetStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Budgets\StoreBudgetRequest;
use App\Models\Branch;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Services\BudgetCreationService;
use App\Services\BudgetVisibilityService;
use App\Services\OrderBudgetService;
use App\Services\OrderVisibilityService;
use App\Support\BudgetPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $request->user();

        $query = app(BudgetVisibilityService::class)->visibleQuery($actor)
            ->with(['customer:id,name,trade_name', 'company:id,trade_name', 'branch:id,name', 'order:id,order_number', 'creator:id,name'])
            ->withCount('items');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($companyId = $request->integer('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($branchId = $request->integer('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($request->filled('linked')) {
            $request->boolean('linked') ? $query->whereNotNull('order_id') : $query->whereNull('order_id');
        }

        if ($from = $request->string('from')->toString()) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->string('to')->toString()) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('budget_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%")
                        ->orWhere('cnpj', 'like', "%{$search}%"))
                    ->orWhereHas('order', fn (Builder $o) => $o->where('order_number', 'like', "%{$search}%"));
            });
        }

        $budgets = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $budgets->through(fn (Budget $budget): array => BudgetPresenter::summary($budget));

        return Inertia::render('budgets/index', [
            'budgets' => $budgets,
            'filters' => $request->only(['status', 'company_id', 'branch_id', 'linked', 'from', 'to', 'search']),
            'statusOptions' => array_map(fn (BudgetStatus $status): string => $status->value, BudgetStatus::cases()),
            'companies' => Company::query()->headquarters()->orderBy('trade_name')->get(['id', 'trade_name']),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name', 'company_id']),
        ]);
    }

    public function create(Request $request): Response
    {
        $actor = $request->user();

        return Inertia::render('budgets/create', [
            'customers' => Customer::query()->where('is_active', true)->orderBy('name')->limit(100)->get(['id', 'name', 'trade_name', 'cpf', 'cnpj', 'type']),
            'orders' => app(OrderVisibilityService::class)->visibleQuery($actor)
                ->with('customer:id,name,trade_name')
                ->latest('id')
                ->limit(50)
                ->get(['id', 'order_number', 'customer_id', 'status']),
            'companies' => Company::query()->headquarters()->orderBy('trade_name')->get(['id', 'trade_name']),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name', 'company_id']),
            'templates' => BudgetTemplate::query()->where('active', true)->with('items')->orderBy('name')->get(),
            'itemTypes' => array_map(fn (BudgetItemType $type): string => $type->value, BudgetItemType::cases()),
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $actor = $request->user();
        $items = $data['budget_template_id'] ?? null ? [] : ($data['items'] ?? []);
        $template = ! empty($data['budget_template_id']) ? BudgetTemplate::query()->findOrFail((int) $data['budget_template_id']) : null;

        try {
            if (! empty($data['order_id'])) {
                $order = Order::query()->findOrFail((int) $data['order_id']);

                $budget = app(OrderBudgetService::class)->createForOrder(
                    $order,
                    $actor,
                    $items,
                    $template,
                    $data['discount_amount'] ?? '0.00',
                    $data['valid_until'] ?? null,
                    $data['notes'] ?? null,
                );
            } else {
                $customer = Customer::query()->findOrFail((int) $data['customer_id']);
                $company = Company::query()->findOrFail((int) $data['company_id']);
                $branch = ! empty($data['branch_id']) ? Branch::query()->findOrFail((int) $data['branch_id']) : null;

                $budget = $template
                    ? app(BudgetCreationService::class)->createStandaloneFromTemplate($template, $customer, $company, $branch, $actor, $data['discount_amount'] ?? '0.00', $data['valid_until'] ?? null, $data['notes'] ?? null)
                    : app(BudgetCreationService::class)->createStandalone($customer, $company, $branch, $actor, $items, $data['discount_amount'] ?? '0.00', $data['valid_until'] ?? null, $data['notes'] ?? null);
            }
        } catch (LogicException $exception) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $exception->getMessage()]);

            return back()->withInput();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Orçamento criado.']);

        return to_route('budgets.show', $budget);
    }

    public function show(Request $request, int $budget): Response
    {
        $actor = $request->user();
        $model = Budget::query()->with(['items', 'customer', 'company', 'branch', 'order', 'creator'])->findOrFail($budget);

        abort_unless(app(BudgetVisibilityService::class)->canView($actor, $model), 403);

        $compatibleOrders = [];
        if ($model->order_id === null && $model->status === BudgetStatus::DRAFT) {
            $compatibleOrders = Order::query()
                ->where('customer_id', $model->customer_id)
                ->latest('id')
                ->limit(20)
                ->get(['id', 'order_number', 'status']);
        }

        return Inertia::render('budgets/show', [
            'budget' => BudgetPresenter::detail($model),
            'itemTypes' => array_map(fn (BudgetItemType $type): string => $type->value, BudgetItemType::cases()),
            'compatibleOrders' => $compatibleOrders,
        ]);
    }
}
