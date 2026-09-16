<?php

namespace App\Services;

use App\Enums\BudgetItemType;
use App\Enums\BudgetStatus;
use App\Models\Branch;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class BudgetCreationService
{
    /** @param array<int, array<string, mixed>> $items */
    public function create(Order $order, User $actor, array $items = [], string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        $this->validateOrderContext($order, $actor);

        return DB::transaction(fn (): Budget => $this->persist(['order_id' => $order->getKey()], $actor, $items, $discountAmount, $validUntil, $notes));
    }

    public function createFromTemplate(Order $order, BudgetTemplate $template, User $actor, string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        $this->validateOrderContext($order, $actor);
        $this->validateTemplate($template);

        return DB::transaction(fn (): Budget => $this->persist(
            ['order_id' => $order->getKey()],
            $actor,
            $this->templateItemsPayload($template),
            $discountAmount,
            $validUntil,
            $notes
        ));
    }

    /**
     * Creates a budget for a customer that is not (yet) linked to a service order.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createStandalone(Customer $customer, Company $company, ?Branch $branch, User $actor, array $items = [], string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        $this->validateStandaloneContext($customer, $company, $branch, $actor);

        return DB::transaction(fn (): Budget => $this->persist(
            ['order_id' => null, 'customer_id' => $customer->getKey(), 'company_id' => $company->getKey(), 'branch_id' => $branch?->getKey()],
            $actor,
            $items,
            $discountAmount,
            $validUntil,
            $notes
        ));
    }

    public function createStandaloneFromTemplate(BudgetTemplate $template, Customer $customer, Company $company, ?Branch $branch, User $actor, string|int $discountAmount = '0.00', ?string $validUntil = null, ?string $notes = null): Budget
    {
        $this->validateStandaloneContext($customer, $company, $branch, $actor);
        $this->validateTemplate($template);

        return DB::transaction(fn (): Budget => $this->persist(
            ['order_id' => null, 'customer_id' => $customer->getKey(), 'company_id' => $company->getKey(), 'branch_id' => $branch?->getKey()],
            $actor,
            $this->templateItemsPayload($template),
            $discountAmount,
            $validUntil,
            $notes
        ));
    }

    /**
     * Links a budget created without a service order to one, once the order exists.
     */
    public function attachToOrder(Budget $budget, Order $order, User $actor): Budget
    {
        $this->validateOrderContext($order, $actor);

        if ((int) $budget->tenant_id !== (int) $order->tenant_id) {
            throw new LogicException('Budget and order must belong to the current tenant.');
        }

        if ($budget->order_id !== null) {
            throw new LogicException('Budget is already linked to an order.');
        }

        if ((int) $budget->customer_id !== (int) $order->customer_id) {
            throw new LogicException('Budget customer must match the order customer.');
        }

        return DB::transaction(function () use ($budget, $order): Budget {
            $budget->update(['order_id' => $order->getKey(), 'company_id' => $order->company_id, 'branch_id' => $order->branch_id]);

            return $budget->fresh('items');
        });
    }

    /**
     * @param  array<string, mixed>  $attributes  budget-level identifiers (order_id and, for standalone budgets, customer_id/company_id/branch_id)
     * @param  array<int, array<string, mixed>>  $items
     */
    private function persist(array $attributes, User $actor, array $items, string|int $discountAmount, ?string $validUntil, ?string $notes): Budget
    {
        $budget = new Budget(array_merge($attributes, [
            'budget_number' => app(TenantSequenceService::class)->next('budgets'),
            'status' => BudgetStatus::DRAFT,
            'valid_until' => $validUntil,
            'discount_amount' => $discountAmount,
            'notes' => $notes,
            'created_by' => $actor->getKey(),
        ]));
        $budget->save();

        $calculatedItems = [];
        foreach ($items as $index => $data) {
            $type = $data['type'] ?? 'other';
            if ($type instanceof BudgetItemType) {
                $type = $type->value;
            }
            if (! is_string($type) || BudgetItemType::tryFrom($type) === null) {
                throw new LogicException('Budget item type is invalid.');
            }
            $calculated = BudgetCalculator::item($data['quantity'] ?? '0', $data['unit_price'] ?? '0', $data['discount_amount'] ?? '0');
            $budget->items()->create(array_merge($data, $calculated, ['type' => $type, 'description' => $data['description'] ?? '', 'sort_order' => $data['sort_order'] ?? $index]));
            $calculatedItems[] = $calculated;
        }
        $totals = BudgetCalculator::budget($calculatedItems, $discountAmount);
        $budget->update($totals);

        return $budget->load('items');
    }

    /** @return array<int, array<string, mixed>> */
    private function templateItemsPayload(BudgetTemplate $template): array
    {
        return $template->items()->get()->map(fn ($item): array => [
            'type' => $item->type->value,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount_amount' => $item->discount_amount,
            'sort_order' => $item->sort_order,
            'notes' => $item->notes,
            'source_template_item_id' => $item->getKey(),
        ])->all();
    }

    private function validateTemplate(BudgetTemplate $template): void
    {
        $tenant = Tenant::current();
        if (! $tenant || (int) $template->tenant_id !== (int) $tenant->getKey() || ! $template->active) {
            throw new LogicException('Budget template must be active and belong to the current tenant.');
        }
    }

    private function validateOrderContext(Order $order, User $actor): void
    {
        $tenant = Tenant::current();
        if (! $tenant || (int) $order->tenant_id !== (int) $tenant->getKey() || (int) $actor->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('Budget order and actor must belong to the current tenant.');
        }
    }

    private function validateStandaloneContext(Customer $customer, Company $company, ?Branch $branch, User $actor): void
    {
        $tenant = Tenant::current();
        if (! $tenant
            || (int) $customer->tenant_id !== (int) $tenant->getKey()
            || (int) $company->tenant_id !== (int) $tenant->getKey()
            || (int) $actor->tenant_id !== (int) $tenant->getKey()
            || ($branch !== null && ((int) $branch->tenant_id !== (int) $tenant->getKey() || (int) $branch->company_id !== (int) $company->getKey()))
        ) {
            throw new LogicException('Budget customer, company, branch and actor must belong to the current tenant.');
        }
    }
}
