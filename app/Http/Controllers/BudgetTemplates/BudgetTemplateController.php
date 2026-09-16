<?php

namespace App\Http\Controllers\BudgetTemplates;

use App\Enums\BudgetItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetTemplates\StoreBudgetTemplateRequest;
use App\Http\Requests\BudgetTemplates\UpdateBudgetTemplateRequest;
use App\Models\BudgetTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BudgetTemplateController extends Controller
{
    public function index(Request $request): Response
    {
        $query = BudgetTemplate::query()->withCount('items');

        if ($search = trim($request->string('search')->toString())) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return Inertia::render('budget-templates/index', [
            'templates' => $query->orderBy('name')->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'active']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('budget-templates/create', [
            'itemTypes' => array_map(fn (BudgetItemType $type): string => $type->value, BudgetItemType::cases()),
        ]);
    }

    public function store(StoreBudgetTemplateRequest $request): RedirectResponse
    {
        $template = BudgetTemplate::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Orçamento pré-definido criado.']);

        return to_route('budget-templates.show', $template);
    }

    public function show(int $budgetTemplate): Response
    {
        $template = BudgetTemplate::query()->with('items')->findOrFail($budgetTemplate);

        return Inertia::render('budget-templates/show', [
            'template' => $this->present($template),
            'itemTypes' => array_map(fn (BudgetItemType $type): string => $type->value, BudgetItemType::cases()),
        ]);
    }

    public function edit(int $budgetTemplate): Response
    {
        $template = BudgetTemplate::query()->with('items')->findOrFail($budgetTemplate);

        return Inertia::render('budget-templates/edit', [
            'template' => $this->present($template),
        ]);
    }

    public function update(UpdateBudgetTemplateRequest $request, int $budgetTemplate): RedirectResponse
    {
        $template = BudgetTemplate::query()->findOrFail($budgetTemplate);
        $template->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Orçamento pré-definido atualizado.']);

        return to_route('budget-templates.show', $template);
    }

    /** @return array<string, mixed> */
    private function present(BudgetTemplate $template): array
    {
        return [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'active' => $template->active,
            'notes' => $template->notes,
            'items' => $template->items->map(fn ($item): array => [
                'id' => $item->id,
                'type' => $item->type->value,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
                'total' => $item->total,
                'sort_order' => $item->sort_order,
                'notes' => $item->notes,
            ])->all(),
        ];
    }
}
