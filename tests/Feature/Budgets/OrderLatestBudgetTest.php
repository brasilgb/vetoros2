<?php

namespace Tests\Feature\Budgets;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BudgetCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderLatestBudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_without_budgets_has_no_latest_budget(): void
    {
        [$order] = $this->orderContext('olb-a');

        $this->assertNull($order->fresh()->latestBudget);
    }

    public function test_an_order_with_a_single_budget_returns_it(): void
    {
        [$order, $user] = $this->orderContext('olb-b');
        $budget = app(BudgetCreationService::class)->create($order, $user);

        $this->assertSame($budget->id, $order->fresh()->latestBudget->id);
    }

    public function test_an_order_with_multiple_budgets_returns_the_most_recent_one(): void
    {
        [$order, $user] = $this->orderContext('olb-c');
        $service = app(BudgetCreationService::class);
        $service->create($order, $user);
        $service->create($order, $user);
        $third = $service->create($order, $user);

        $this->assertSame($third->id, $order->fresh()->latestBudget->id);
        $this->assertSame(3, $order->fresh()->latestBudget->budget_number);
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
