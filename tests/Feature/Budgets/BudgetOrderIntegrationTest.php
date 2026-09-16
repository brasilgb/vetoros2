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
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class BudgetOrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_for_an_order_inherits_customer_company_and_branch(): void
    {
        [, $order] = $this->context('boi-a');
        $budget = app(BudgetCreationService::class)->create($order, $order->creator);

        $this->assertSame($order->customer_id, $budget->customer_id);
        $this->assertSame($order->company_id, $budget->company_id);
        $this->assertSame($order->branch_id, $budget->branch_id);
    }

    public function test_budget_can_be_created_without_an_order_and_attached_later(): void
    {
        [$tenant, , $company, $branch, $customer, $user] = $this->context('boi-b');
        $service = app(BudgetCreationService::class);

        $budget = $service->createStandalone($customer, $company, $branch, $user);
        $this->assertNull($budget->order_id);
        $this->assertSame($customer->id, $budget->customer_id);

        $order = Order::factory()->create([
            'tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id,
            'customer_id' => $customer->id, 'equipment_type_id' => EquipmentType::factory()->create(['tenant_id' => $tenant->id])->id,
            'created_by' => $user->id,
        ]);

        $attached = $service->attachToOrder($budget, $order, $user);
        $this->assertSame($order->id, $attached->order_id);
        $this->assertCount(1, $order->fresh()->budgets);
    }

    public function test_standalone_budget_cannot_be_attached_to_an_order_of_a_different_customer(): void
    {
        [$tenant, , $company, $branch, $customer, $user] = $this->context('boi-c');
        $otherCustomer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $service = app(BudgetCreationService::class);
        $budget = $service->createStandalone($customer, $company, $branch, $user);

        $order = Order::factory()->create([
            'tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id,
            'customer_id' => $otherCustomer->id, 'equipment_type_id' => EquipmentType::factory()->create(['tenant_id' => $tenant->id])->id,
            'created_by' => $user->id,
        ]);

        $this->expectException(LogicException::class);
        $service->attachToOrder($budget, $order, $user);
    }

    public function test_budget_created_directly_with_a_conflicting_customer_is_rejected(): void
    {
        [$tenant, $order, , , , $user] = $this->context('boi-d');
        $otherCustomer = Customer::factory()->create(['tenant_id' => $tenant->id]);

        $this->expectException(LogicException::class);
        Budget::create([
            'order_id' => $order->id, 'customer_id' => $otherCustomer->id, 'budget_number' => 999, 'created_by' => $user->id,
        ]);
    }

    public function test_budget_created_directly_with_a_conflicting_company_is_rejected(): void
    {
        [$tenant, $order, $company, , , $user] = $this->context('boi-f');
        $otherCompany = Company::factory()->branch($company)->create(['tenant_id' => $tenant->id]);

        $this->expectException(LogicException::class);
        Budget::create([
            'order_id' => $order->id, 'company_id' => $otherCompany->id, 'budget_number' => 999, 'created_by' => $user->id,
        ]);
    }

    public function test_standalone_budget_from_template_copies_items_independently(): void
    {
        [$tenant, , $company, $branch, $customer, $user] = $this->context('boi-e');
        $template = BudgetTemplate::factory()->create();
        $template->items()->create(['tenant_id' => $tenant->id, 'type' => 'part', 'description' => 'Bateria', 'quantity' => '1.000', 'unit_price' => '80.00', 'discount_amount' => '0.00', 'total' => '80.00']);

        $budget = app(BudgetCreationService::class)->createStandaloneFromTemplate($template, $customer, $company, $branch, $user);

        $this->assertNull($budget->order_id);
        $this->assertSame($customer->id, $budget->customer_id);
        $this->assertSame('80.00', $budget->fresh()->total);
        $this->assertCount(1, $budget->items);
    }

    /** @return array{0: Tenant, 1: Order, 2: Company, 3: Branch, 4: Customer, 5: User} */
    private function context(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $order = Order::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id, 'created_by' => $user->id]);

        return [$tenant, $order, $company, $branch, $customer, $user];
    }
}
