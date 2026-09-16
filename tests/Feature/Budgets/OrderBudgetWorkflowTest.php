<?php

namespace Tests\Feature\Budgets;

use App\Enums\BudgetStatus;
use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\BudgetTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BudgetCreationService;
use App\Services\OrderBudgetService;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderBudgetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_send_and_approval_are_orchestrated_atomically(): void
    {
        [$order, $user] = $this->orderContext('obw-a');
        $service = app(OrderBudgetService::class);
        $budget = $service->createForOrder($order, $user, [['type' => 'service', 'description' => 'Diagnóstico', 'quantity' => '1', 'unit_price' => '100.00']]);

        $this->assertSame(BudgetStatus::DRAFT, $budget->status);
        $this->assertSame(OrderStatus::OPEN, $order->fresh()->status);
        $service->send($budget, $user);
        $approved = $service->approve($budget, $user);

        $this->assertSame(BudgetStatus::APPROVED, $approved->status);
        $this->assertSame(OrderStatus::BUDGET_APPROVED, $order->fresh()->status);
        $this->assertTrue($approved->canApprove() === false && $approved->canEdit() === false);
    }

    public function test_rejection_reopen_and_new_budget_preserve_the_first_budget(): void
    {
        [$order, $user] = $this->orderContext('obw-b');
        $service = app(OrderBudgetService::class);
        $first = $service->createForOrder($order, $user, [['type' => 'service', 'description' => 'Opção A', 'quantity' => '1', 'unit_price' => '100.00']]);
        $service->send($first, $user);
        $service->reject($first, $user);
        $this->assertSame(OrderStatus::BUDGET_REJECTED, $order->fresh()->status);
        $service->reopenAfterRejection($first->fresh(), $order->fresh(), $user);
        $this->assertSame(OrderStatus::OPEN, $order->fresh()->status);
        $second = $service->createForOrder($order->fresh(), $user, [['type' => 'service', 'description' => 'Opção B', 'quantity' => '1', 'unit_price' => '200.00']]);
        $service->send($second, $user);
        $service->approve($second, $user);

        $this->assertSame(BudgetStatus::REJECTED, $first->fresh()->status);
        $this->assertSame('100.00', $first->fresh()->total);
        $this->assertSame(BudgetStatus::APPROVED, $second->fresh()->status);
        $this->assertSame(OrderStatus::BUDGET_APPROVED, $order->fresh()->status);
        $this->assertCount(2, $order->fresh()->budgets);
    }

    public function test_reopen_rejects_a_budget_that_belongs_to_a_different_order(): void
    {
        [$orderA, $userA] = $this->orderContext('obw-l');
        $service = app(OrderBudgetService::class);
        $budgetA = $service->createForOrder($orderA, $userA);
        $service->send($budgetA, $userA);
        $service->reject($budgetA, $userA);

        $orderB = Order::factory()->create([
            'tenant_id' => $orderA->tenant_id, 'company_id' => $orderA->company_id, 'branch_id' => $orderA->branch_id,
            'customer_id' => $orderA->customer_id, 'equipment_type_id' => $orderA->equipment_type_id, 'created_by' => $userA->id,
        ]);

        $this->expectException(LogicException::class);
        $service->reopenAfterRejection($budgetA->fresh(), $orderB, $userA);
    }

    public function test_reopen_rejects_a_budget_from_another_tenant(): void
    {
        [$orderA, $userA] = $this->orderContext('obw-m');
        $service = app(OrderBudgetService::class);
        $budgetA = $service->createForOrder($orderA, $userA);
        $service->send($budgetA, $userA);
        $rejectedA = $service->reject($budgetA, $userA);

        [$orderB, $userB] = $this->orderContext('obw-m-other');

        $this->expectException(LogicException::class);
        $service->reopenAfterRejection($rejectedA, $orderB, $userB);
    }

    public function test_reopen_rejects_a_budget_that_is_not_rejected(): void
    {
        [$order, $user] = $this->orderContext('obw-n');
        $service = app(OrderBudgetService::class);
        $budget = $service->createForOrder($order, $user);
        $service->send($budget, $user);

        $this->expectException(LogicException::class);
        $service->reopenAfterRejection($budget->fresh(), $order, $user);
    }

    public function test_standalone_send_and_approval_do_not_query_an_order(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'obw-c']);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $budget = app(BudgetCreationService::class)->createStandalone($customer, $company, $branch, $user);
        $service = app(OrderBudgetService::class);

        $service->send($budget, $user);
        $approved = $service->approve($budget, $user);

        $this->assertNull($approved->order_id);
        $this->assertSame(BudgetStatus::APPROVED, $approved->status);
    }

    public function test_standalone_rejection_does_not_require_an_order(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'obw-e']);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $budget = app(BudgetCreationService::class)->createStandalone($customer, $company, $branch, $user);
        $service = app(OrderBudgetService::class);

        $service->send($budget, $user);
        $rejected = $service->reject($budget, $user);

        $this->assertNull($rejected->order_id);
        $this->assertSame(BudgetStatus::REJECTED, $rejected->status);
    }

    public function test_cancelling_a_draft_budget_does_not_change_the_order_status(): void
    {
        [$order, $user] = $this->orderContext('obw-f');
        $budget = app(OrderBudgetService::class)->createForOrder($order, $user);

        $cancelled = app(OrderBudgetService::class)->cancel($budget, $user);

        $this->assertSame(BudgetStatus::CANCELLED, $cancelled->status);
        $this->assertSame(OrderStatus::OPEN, $order->fresh()->status);
    }

    public function test_cancelling_a_sent_budget_leaves_the_order_status_untouched(): void
    {
        [$order, $user] = $this->orderContext('obw-g');
        $service = app(OrderBudgetService::class);
        $budget = $service->createForOrder($order, $user);
        $service->send($budget, $user);
        $this->assertSame(OrderStatus::BUDGET_GENERATED, $order->fresh()->status);

        $cancelled = $service->cancel($budget, $user);

        $this->assertSame(BudgetStatus::CANCELLED, $cancelled->status);
        $this->assertSame(OrderStatus::BUDGET_GENERATED, $order->fresh()->status);
    }

    public function test_items_of_a_sent_budget_cannot_be_changed(): void
    {
        [$order, $user] = $this->orderContext('obw-h');
        $service = app(OrderBudgetService::class);
        $budget = $service->createForOrder($order, $user, [['type' => 'service', 'description' => 'Diagnóstico', 'quantity' => '1', 'unit_price' => '100.00']]);
        $service->send($budget, $user);

        $item = $budget->fresh()->items->first();
        $this->expectException(LogicException::class);
        $item->update(['unit_price' => '999.00']);
    }

    public function test_create_for_order_rejects_an_order_from_another_tenant(): void
    {
        [$order, $user] = $this->orderContext('obw-i');
        $otherTenant = Tenant::factory()->create(['slug' => 'obw-i-other']);
        $otherTenant->makeCurrent();

        $this->expectException(LogicException::class);
        app(OrderBudgetService::class)->createForOrder($order, $user);
    }

    public function test_create_for_order_from_template_rejects_a_template_from_another_tenant(): void
    {
        [$order, $user] = $this->orderContext('obw-k');
        $orderTenant = Tenant::findOrFail($order->tenant_id);

        $otherTenant = Tenant::factory()->create(['slug' => 'obw-k-other']);
        $otherTenant->makeCurrent();
        $template = BudgetTemplate::factory()->create();

        $orderTenant->makeCurrent();

        $this->expectException(LogicException::class);
        app(OrderBudgetService::class)->createForOrderFromTemplate($order, $template, $user);
    }

    public function test_send_rejects_an_actor_from_another_tenant(): void
    {
        [$order, $user] = $this->orderContext('obw-j');
        $budget = app(OrderBudgetService::class)->createForOrder($order, $user);
        $otherTenant = Tenant::factory()->create(['slug' => 'obw-j-other']);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        $this->expectException(LogicException::class);
        app(OrderBudgetService::class)->send($budget, $otherUser);
    }

    public function test_send_rolls_back_budget_when_order_transition_fails(): void
    {
        [$order, $user] = $this->orderContext('obw-d');
        $budget = app(OrderBudgetService::class)->createForOrder($order, $user);
        $status = app(OrderStatusService::class);
        $order = $status->transition($order, OrderStatus::BUDGET_GENERATED, $user);
        $order = $status->transition($order, OrderStatus::BUDGET_APPROVED, $user);

        try {
            app(OrderBudgetService::class)->send($budget, $user);
            $this->fail('Expected the order transition to fail.');
        } catch (LogicException) {
            // The transaction must restore the draft status.
        }
        $this->assertSame(BudgetStatus::DRAFT, $budget->fresh()->status);
    }

    /** @return array{0: Order, 1: User} */
    private function orderContext(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $order = Order::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id, 'created_by' => $user->id]);

        return [$order, $user];
    }
}
