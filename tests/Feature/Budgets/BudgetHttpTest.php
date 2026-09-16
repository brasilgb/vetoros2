<?php

namespace Tests\Feature\Budgets;

use App\Models\Branch;
use App\Models\Budget;
use App\Models\BudgetTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BudgetCreationService;
use App\Services\CompanyAccessService;
use App\Services\OrderBudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_only_lists_budgets_of_the_current_tenant(): void
    {
        $contextA = $this->context('bh-a');
        $this->createBudgetForOrder($contextA);

        $contextB = $this->context('bh-b');
        $this->createBudgetForOrder($contextB);

        $contextA['tenant']->makeCurrent();

        $response = $this->actingAs($contextA['user'])->get(route('budgets.index'));

        $response->assertOk();
        $this->assertCount(1, $response->inertiaProps('budgets.data'));
    }

    public function test_create_page_exposes_the_expected_props(): void
    {
        $context = $this->context('bh-c');

        $response = $this->actingAs($context['user'])->get(route('budgets.create'));

        $response->assertOk()->assertInertia(fn ($page) => $page
            ->component('budgets/create')
            ->has('customers')
            ->has('orders')
            ->has('companies')
            ->has('branches')
            ->has('templates')
            ->has('itemTypes'));
    }

    public function test_store_creates_a_budget_for_an_existing_order(): void
    {
        $context = $this->context('bh-d');
        $order = $this->createOrder($context);

        $response = $this->actingAs($context['user'])->post(route('budgets.store'), [
            'order_id' => $order->id,
            'items' => [
                ['type' => 'service', 'description' => 'Diagnóstico', 'quantity' => '1', 'unit_price' => '100.00'],
            ],
        ]);

        $context['tenant']->makeCurrent();
        $budget = Budget::query()->where('order_id', $order->id)->firstOrFail();
        $response->assertRedirect(route('budgets.show', $budget));
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Orçamento criado.']);
        $this->assertSame($context['customer']->id, $budget->customer_id);
        $this->assertSame($context['company']->id, $budget->company_id);
    }

    public function test_store_creates_a_budget_for_an_order_from_a_template(): void
    {
        $context = $this->context('bh-e');
        $order = $this->createOrder($context);
        $template = BudgetTemplate::factory()->create();
        $template->items()->create(['tenant_id' => $context['tenant']->id, 'type' => 'part', 'description' => 'Bateria', 'quantity' => '1.000', 'unit_price' => '80.00', 'discount_amount' => '0.00', 'total' => '80.00']);

        $response = $this->actingAs($context['user'])->post(route('budgets.store'), [
            'order_id' => $order->id,
            'budget_template_id' => $template->id,
        ]);

        $context['tenant']->makeCurrent();
        $budget = Budget::query()->where('order_id', $order->id)->firstOrFail();
        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertCount(1, $budget->items);
        $this->assertSame('80.00', $budget->fresh()->total);
    }

    public function test_store_creates_a_standalone_budget_without_an_order(): void
    {
        $context = $this->context('bh-f');

        $response = $this->actingAs($context['user'])->post(route('budgets.store'), [
            'customer_id' => $context['customer']->id,
            'company_id' => $context['company']->id,
            'branch_id' => $context['branch']->id,
            'items' => [
                ['type' => 'service', 'description' => 'Consultoria', 'quantity' => '1', 'unit_price' => '250.00'],
            ],
        ]);

        $context['tenant']->makeCurrent();
        $budget = Budget::query()->where('customer_id', $context['customer']->id)->whereNull('order_id')->firstOrFail();
        $response->assertRedirect(route('budgets.show', $budget));
        $this->assertNull($budget->order_id);
    }

    public function test_store_requires_either_an_order_or_a_customer_and_company(): void
    {
        $context = $this->context('bh-g');

        $response = $this->actingAs($context['user'])->post(route('budgets.store'), []);

        $response->assertSessionHasErrors(['customer_id', 'company_id']);
    }

    public function test_store_rejects_an_order_from_another_tenant(): void
    {
        $context = $this->context('bh-h');
        $otherContext = $this->context('bh-h-other');
        $otherOrder = $this->createOrder($otherContext);
        $context['tenant']->makeCurrent();

        $response = $this->actingAs($context['user'])->post(route('budgets.store'), [
            'order_id' => $otherOrder->id,
        ]);

        $response->assertSessionHasErrors(['order_id']);
    }

    public function test_show_returns_not_found_for_a_budget_of_another_tenant(): void
    {
        $context = $this->context('bh-i');
        $otherContext = $this->context('bh-i-other');
        $otherBudget = $this->createBudgetForOrder($otherContext);
        $context['tenant']->makeCurrent();

        $this->actingAs($context['user'])
            ->get(route('budgets.show', $otherBudget))
            ->assertNotFound();
    }

    public function test_send_approve_workflow_moves_the_order_through_http(): void
    {
        $context = $this->context('bh-j');
        $budget = $this->createBudgetForOrder($context);

        $this->actingAs($context['user'])
            ->post(route('budgets.send', $budget))
            ->assertRedirect();

        $this->actingAs($context['user'])
            ->post(route('budgets.approve', $budget))
            ->assertRedirect()
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Orçamento aprovado.']);

        $context['tenant']->makeCurrent();
        $this->assertSame('approved', $budget->fresh()->status->value);
        $this->assertSame('budget_approved', $budget->fresh()->order->status->value);
    }

    public function test_reject_and_reopen_workflow_through_http(): void
    {
        $context = $this->context('bh-k');
        $budget = $this->createBudgetForOrder($context);

        $this->actingAs($context['user'])->post(route('budgets.send', $budget));
        $this->actingAs($context['user'])->post(route('budgets.reject', $budget))->assertRedirect();

        $context['tenant']->makeCurrent();
        $this->assertSame('budget_rejected', $budget->fresh()->order->status->value);

        $this->actingAs($context['user'])
            ->post(route('budgets.reopen', $budget))
            ->assertRedirect()
            ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Ordem de serviço reaberta.']);

        $context['tenant']->makeCurrent();
        $this->assertSame('open', $budget->fresh()->order->status->value);
    }

    public function test_invalid_transition_surfaces_as_a_flash_error_instead_of_a_crash(): void
    {
        $context = $this->context('bh-l');
        $budget = $this->createBudgetForOrder($context);

        $response = $this->actingAs($context['user'])->post(route('budgets.approve', $budget));

        $response->assertRedirect();
        $response->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'Cannot transition budget from draft to approved.']);
        $context['tenant']->makeCurrent();
        $this->assertSame('draft', $budget->fresh()->status->value);
    }

    public function test_items_can_be_managed_while_draft_and_are_blocked_once_sent(): void
    {
        $context = $this->context('bh-m');
        $order = $this->createOrder($context);
        $budget = app(OrderBudgetService::class)->createForOrder($order, $context['user']);

        $this->actingAs($context['user'])->post(route('budgets.items.store', $budget), [
            'type' => 'service', 'description' => 'Item A', 'quantity' => '1', 'unit_price' => '50.00',
        ])->assertRedirect();

        $context['tenant']->makeCurrent();
        $item = $budget->fresh()->items->first();
        $this->assertSame('50.00', $budget->fresh()->total);

        $this->actingAs($context['user'])->put(route('budgets.items.update', [$budget, $item]), [
            'type' => 'service', 'description' => 'Item A editado', 'quantity' => '2', 'unit_price' => '50.00',
        ])->assertRedirect();
        $context['tenant']->makeCurrent();
        $this->assertSame('100.00', $budget->fresh()->total);

        $this->actingAs($context['user'])->post(route('budgets.send', $budget));

        $response = $this->actingAs($context['user'])->post(route('budgets.items.store', $budget), [
            'type' => 'service', 'description' => 'Item B', 'quantity' => '1', 'unit_price' => '10.00',
        ]);
        $response->assertRedirect();
        $response->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'Items of a budget that is no longer a draft cannot be changed.']);
        $context['tenant']->makeCurrent();
        $this->assertCount(1, $budget->fresh()->items);
    }

    public function test_attach_to_order_links_a_standalone_budget(): void
    {
        $context = $this->context('bh-n');
        $budget = app(BudgetCreationService::class)->createStandalone($context['customer'], $context['company'], $context['branch'], $context['user']);
        $order = $this->createOrder($context);

        $response = $this->actingAs($context['user'])->post(route('budgets.attach-to-order', $budget), [
            'order_id' => $order->id,
        ]);

        $response->assertRedirect();
        $response->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Orçamento vinculado à Ordem de Serviço.']);
        $context['tenant']->makeCurrent();
        $this->assertSame($order->id, $budget->fresh()->order_id);
    }

    public function test_attach_to_order_with_a_different_customer_flashes_an_error(): void
    {
        $context = $this->context('bh-o');
        $budget = app(BudgetCreationService::class)->createStandalone($context['customer'], $context['company'], $context['branch'], $context['user']);
        $otherCustomer = Customer::factory()->create(['tenant_id' => $context['tenant']->id]);
        $order = $this->createOrder($context, $otherCustomer);

        $response = $this->actingAs($context['user'])->post(route('budgets.attach-to-order', $budget), [
            'order_id' => $order->id,
        ]);

        $response->assertRedirect();
        $response->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'Budget customer must match the order customer.']);
        $context['tenant']->makeCurrent();
        $this->assertNull($budget->fresh()->order_id);
    }

    /** @return array{tenant: Tenant, company: Company, branch: Branch, user: User, customer: Customer} */
    private function context(string $slug): array
    {
        $tenant = Tenant::create(['name' => $slug, 'slug' => $slug, 'active' => true]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        app(CompanyAccessService::class)->grant($user, $company, true);
        $branch->users()->attach($user->id, ['tenant_id' => $tenant->id]);

        return compact('tenant', 'company', 'branch', 'user', 'customer');
    }

    /** @param array{tenant: Tenant, company: Company, branch: Branch, user: User, customer: Customer} $context */
    private function createOrder(array $context, ?Customer $customer = null): Order
    {
        $context['tenant']->makeCurrent();
        $type = EquipmentType::factory()->create(['tenant_id' => $context['tenant']->id]);

        return Order::factory()->create([
            'tenant_id' => $context['tenant']->id,
            'company_id' => $context['company']->id,
            'branch_id' => $context['branch']->id,
            'customer_id' => ($customer ?? $context['customer'])->id,
            'equipment_type_id' => $type->id,
            'created_by' => $context['user']->id,
        ]);
    }

    /** @param array{tenant: Tenant, company: Company, branch: Branch, user: User, customer: Customer} $context */
    private function createBudgetForOrder(array $context): Budget
    {
        $order = $this->createOrder($context);

        return app(OrderBudgetService::class)->createForOrder($order, $context['user'], [
            ['type' => 'service', 'description' => 'Diagnóstico', 'quantity' => '1', 'unit_price' => '100.00'],
        ]);
    }
}
