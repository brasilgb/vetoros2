<?php

namespace Tests\Feature\Orders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderOperationalVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_matrix_user_sees_all_branches_and_branch_user_sees_only_its_branch(): void
    {
        [$tenant, $company, $branchA, $branchB] = $this->structure('visibility');
        $matrix = User::factory()->create(['tenant_id' => $tenant->id]);
        $branchUser = User::factory()->create(['tenant_id' => $tenant->id]);
        $company->users()->attach($matrix->id, ['tenant_id' => $tenant->id]);
        $branchA->users()->attach($branchUser->id, ['tenant_id' => $tenant->id]);
        $orderA = $this->order($tenant, $company, $branchA);
        $orderB = $this->order($tenant, $company, $branchB);

        $tenant->makeCurrent();
        $service = app(OrderVisibilityService::class);

        $this->assertSame([$orderA->id, $orderB->id], $service->visibleQuery($matrix)->orderBy('id')->pluck('id')->all());
        $this->assertSame([$orderA->id], $service->visibleQuery($branchUser)->pluck('id')->all());
    }

    public function test_branch_and_order_cannot_cross_tenant_or_company(): void
    {
        [$tenantA, $companyA, $branchA] = $this->structure('tenant-a');
        [$tenantB, $companyB, $branchB] = $this->structure('tenant-b');
        $tenantA->makeCurrent();

        $this->expectException(\LogicException::class);
        $this->order($tenantA, $companyA, $branchB);
    }

    /** @return array{Tenant, Company, Branch, Branch} */
    private function structure(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);

        return [$tenant, $company, Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'A']), Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'B'])];
    }

    private function order(Tenant $tenant, Company $company, Branch $branch): Order
    {
        $tenant->makeCurrent();
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);

        return Order::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id]);
    }
}
