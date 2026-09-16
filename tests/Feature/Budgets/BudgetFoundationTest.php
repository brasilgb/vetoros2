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
use App\Services\BudgetCalculator;
use App\Services\BudgetCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class BudgetFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_uses_decimal_values_without_float_drift(): void
    {
        $item = BudgetCalculator::item('1.500', '19.99', '2.00');
        $this->assertSame('1.500', $item['quantity']);
        $this->assertSame('19.99', $item['unit_price']);
        $this->assertSame('2.00', $item['discount_amount']);
        $this->assertSame('27.99', $item['total']);
        $this->assertSame(['subtotal' => '27.99', 'discount_amount' => '0.99', 'total' => '27.00'], BudgetCalculator::budget([$item], '0.99'));
    }

    public function test_template_is_materialized_and_can_change_independently(): void
    {
        [$tenant, $order, $user] = $this->orderData('budget-a');
        $tenant->makeCurrent();
        $template = BudgetTemplate::factory()->create();
        $templateItem = $template->items()->create(['tenant_id' => $tenant->id, 'type' => 'service', 'description' => 'Tela OLED', 'quantity' => '1.000', 'unit_price' => '350.00', 'discount_amount' => '0.00', 'total' => '350.00']);

        $budget = app(BudgetCreationService::class)->createFromTemplate($order, $template, $user);
        $templateItem->update(['unit_price' => '999.00']);
        $this->assertSame('350.00', $budget->fresh()->items->first()->unit_price);
        $this->assertSame($templateItem->id, $budget->items->first()->source_template_item_id);
    }

    public function test_an_order_can_have_multiple_budgets_and_numbers_are_tenant_scoped(): void
    {
        [$tenant, $order, $user] = $this->orderData('budget-b');
        $tenant->makeCurrent();
        $service = app(BudgetCreationService::class);
        $first = $service->create($order, $user, []);
        $second = $service->create($order, $user, []);
        $this->assertSame(1, $first->budget_number);
        $this->assertSame(2, $second->budget_number);
        $this->assertCount(2, $order->fresh()->budgets);
    }

    public function test_order_from_another_tenant_is_rejected(): void
    {
        [$tenantA, $orderA, $userA] = $this->orderData('budget-c');
        [$tenantB] = $this->orderData('budget-d');
        $tenantB->makeCurrent();
        $this->expectException(LogicException::class);
        app(BudgetCreationService::class)->create($orderA, $userA);
        $this->assertNull(Budget::withoutGlobalScopes()->where('tenant_id', $tenantA->id)->first());
    }

    /** @return array{0: Tenant, 1: Order, 2: User} */
    private function orderData(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $order = Order::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id, 'created_by' => $user->id]);

        return [$tenant, $order, $user];
    }
}
