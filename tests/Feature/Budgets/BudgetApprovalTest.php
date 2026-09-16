<?php

namespace Tests\Feature\Budgets;

use App\Enums\BudgetStatus;
use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BudgetApprovalService;
use App\Services\BudgetCreationService;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class BudgetApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_budget_moves_the_order_from_budget_generated_to_budget_approved(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-a');
        $budget = app(BudgetCreationService::class)->create($order, $user);
        $service = app(BudgetApprovalService::class);

        $service->send($budget, $user);
        $approved = $service->approve($budget, $user);

        $this->assertSame(BudgetStatus::APPROVED, $approved->status);
        $this->assertSame(OrderStatus::BUDGET_APPROVED, $order->fresh()->status);
    }

    public function test_rejected_budget_moves_the_order_from_budget_generated_to_budget_rejected(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-b');
        $budget = app(BudgetCreationService::class)->create($order, $user);
        $service = app(BudgetApprovalService::class);

        $service->send($budget, $user);
        $rejected = $service->reject($budget, $user);

        $this->assertSame(BudgetStatus::REJECTED, $rejected->status);
        $this->assertSame(OrderStatus::BUDGET_REJECTED, $order->fresh()->status);
    }

    public function test_items_of_an_approved_budget_cannot_be_changed(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-c');
        $budget = app(BudgetCreationService::class)->create($order, $user, [
            ['type' => 'service', 'description' => 'Diagnostico', 'quantity' => '1', 'unit_price' => '50.00'],
        ]);
        $service = app(BudgetApprovalService::class);
        $service->send($budget, $user);
        $service->approve($budget, $user);

        $item = $budget->fresh()->items->first();
        $this->expectException(LogicException::class);
        $item->update(['unit_price' => '999.00']);
    }

    public function test_approving_a_draft_budget_without_sending_first_is_rejected(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-d');
        $budget = app(BudgetCreationService::class)->create($order, $user);

        $this->expectException(LogicException::class);
        app(BudgetApprovalService::class)->approve($budget, $user);
    }

    public function test_budget_status_cannot_be_changed_directly(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-e');
        $budget = app(BudgetCreationService::class)->create($order, $user);

        $this->expectException(LogicException::class);
        $budget->update(['status' => BudgetStatus::APPROVED]);
    }

    public function test_approval_actor_from_another_tenant_is_rejected(): void
    {
        [$order] = $this->orderWithBudgetGenerated('bat-f');
        $otherTenant = Tenant::factory()->create(['slug' => 'bat-f-other']);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $budget = app(BudgetCreationService::class)->create($order, $order->creator);

        $this->expectException(LogicException::class);
        app(BudgetApprovalService::class)->send($budget, $otherUser);
    }

    public function test_draft_budget_can_be_cancelled(): void
    {
        [$order, $user] = $this->orderWithBudgetGenerated('bat-g');
        $budget = app(BudgetCreationService::class)->create($order, $user);

        $cancelled = app(BudgetApprovalService::class)->cancel($budget, $user);

        $this->assertSame(BudgetStatus::CANCELLED, $cancelled->status);
    }

    public function test_standalone_budget_without_an_order_can_be_approved(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'bat-h']);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $budget = app(BudgetCreationService::class)->createStandalone($customer, $company, $branch, $user);
        $service = app(BudgetApprovalService::class);

        $service->send($budget, $user);
        $approved = $service->approve($budget, $user);

        $this->assertSame(BudgetStatus::APPROVED, $approved->status);
    }

    /** @return array{0: Order, 1: User} */
    private function orderWithBudgetGenerated(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $order = Order::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id, 'created_by' => $user->id]);
        $order = app(OrderStatusService::class)->transition($order, OrderStatus::BUDGET_GENERATED, $user);

        return [$order, $user];
    }
}
