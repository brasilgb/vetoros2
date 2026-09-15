<?php

namespace Tests\Feature\Orders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\OrderSnapshot;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderCreationService;
use App\Services\OrderSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_generates_one_snapshot_with_the_historical_context(): void
    {
        [$tenant, $company, $branch, $customer, $equipment, $type] = $this->context('snapshot');
        $tenant->makeCurrent();
        $order = Order::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'customer_equipment_id' => $equipment->id,
            'equipment_type_id' => $type->id,
        ]);

        $snapshot = app(OrderSnapshotService::class)->create($order);
        $this->assertNotNull($snapshot);
        $this->assertSame(1, OrderSnapshot::where('order_id', $order->id)->count());
        $this->assertSame($tenant->id, $snapshot->tenant_id);
        $this->assertSame($customer->phone, $snapshot->customer_data['phone']);
        $this->assertSame($equipment->serial_number, $snapshot->equipment_data['serial_number']);
        $this->assertSame($company->trade_name, $snapshot->company_data['trade_name']);
        $this->assertSame($branch->name, $snapshot->branch_data['name']);
    }

    public function test_official_order_creation_service_assigns_the_transactional_number(): void
    {
        [$tenant, $company, $branch, $customer, , $type] = $this->context('official-number');
        $tenant->makeCurrent();
        $actor = User::factory()->create(['tenant_id' => $tenant->id]);
        $order = app(OrderCreationService::class)->create([
            'order' => [
                'company_id' => $company->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'equipment_type_id' => $type->id,
                'reported_issue' => 'Issue',
                'created_by' => $actor->id,
            ],
        ], $actor);

        $this->assertSame(1, $order->order_number);
        $this->assertNotNull($order->snapshot);
    }

    public function test_master_changes_do_not_change_the_snapshot(): void
    {
        [$tenant, $company, $branch, $customer, $equipment, $type] = $this->context('master-changes');
        $tenant->makeCurrent();
        $order = Order::factory()->create([
            'company_id' => $company->id, 'branch_id' => $branch->id,
            'customer_id' => $customer->id, 'customer_equipment_id' => $equipment->id,
            'equipment_type_id' => $type->id,
        ]);
        $original = app(OrderSnapshotService::class)->create($order);
        $customer->update(['phone' => '222222222']);
        $equipment->update(['serial_number' => 'XYZ']);
        $company->update(['trade_name' => 'Matriz atualizada']);
        $branch->update(['name' => 'Unidade atualizada']);

        $this->assertSame('222222222', $order->fresh()->customer->phone);
        $this->assertSame('111111111', $original->fresh()->customer_data['phone']);
        $this->assertSame('ABC', $original->fresh()->equipment_data['serial_number']);
        $this->assertSame('Matriz original', $original->fresh()->company_data['trade_name']);
        $this->assertSame('Unidade original', $original->fresh()->branch_data['name']);
    }

    public function test_order_without_physical_equipment_preserves_the_equipment_type(): void
    {
        [$tenant, $company, $branch, $customer, , $type] = $this->context('without-equipment');
        $tenant->makeCurrent();
        $order = Order::factory()->create([
            'company_id' => $company->id, 'branch_id' => $branch->id,
            'customer_id' => $customer->id, 'customer_equipment_id' => null,
            'equipment_type_id' => $type->id,
        ]);

        app(OrderSnapshotService::class)->create($order);
        $this->assertSame($type->name, $order->snapshot->equipment_data['equipment_type']['name']);
    }

    public function test_snapshot_is_immutable_and_duplicate_is_rejected(): void
    {
        [$tenant, $company, $branch, $customer, $equipment, $type] = $this->context('immutable-snapshot');
        $tenant->makeCurrent();
        $order = Order::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'customer_equipment_id' => $equipment->id, 'equipment_type_id' => $type->id]);
        $snapshot = app(OrderSnapshotService::class)->create($order);

        try {
            $snapshot->update(['customer_data' => ['name' => 'changed']]);
            $this->fail('The snapshot should be immutable.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(LogicException::class);
        $snapshot->delete();
    }

    public function test_duplicate_snapshot_is_rejected(): void
    {
        [$tenant, $company, $branch, $customer, $equipment, $type] = $this->context('duplicate-snapshot');
        $tenant->makeCurrent();
        $order = Order::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'customer_equipment_id' => $equipment->id, 'equipment_type_id' => $type->id]);
        app(OrderSnapshotService::class)->create($order);

        $this->expectException(LogicException::class);
        app(OrderSnapshotService::class)->create($order);
    }

    public function test_official_creation_service_rolls_back_when_order_validation_fails(): void
    {
        [$tenant, $company, $branch, $customer, $equipment, $type] = $this->context('atomic-creation');
        $tenant->makeCurrent();

        try {
            app(OrderCreationService::class)->create([
                'order' => [
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                    'customer_id' => $customer->id,
                    'customer_equipment_id' => $equipment->id,
                    'equipment_type_id' => $type->id,
                    'created_by' => 999999,
                ],
            ], User::factory()->create(['tenant_id' => $tenant->id]));
            $this->fail('The invalid order should fail.');
        } catch (LogicException) {
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('order_snapshots', 0);
        }
    }

    public function test_snapshot_rejects_an_order_from_another_tenant(): void
    {
        [$tenantA, $companyA, $branchA, $customerA, $equipmentA, $typeA] = $this->context('snapshot-a');
        [$tenantB, $companyB, $branchB, $customerB, $equipmentB, $typeB] = $this->context('snapshot-b');
        $tenantB->makeCurrent();
        $orderB = Order::factory()->create(['company_id' => $companyB->id, 'branch_id' => $branchB->id, 'customer_id' => $customerB->id, 'customer_equipment_id' => $equipmentB->id, 'equipment_type_id' => $typeB->id]);
        $tenantA->makeCurrent();

        $this->expectException(LogicException::class);
        OrderSnapshot::withoutGlobalScopes()->create(['tenant_id' => $tenantA->id, 'order_id' => $orderB->id, 'customer_data' => [], 'company_data' => [], 'branch_data' => []]);
    }

    /** @return array{Tenant, Company, Branch, Customer, CustomerEquipment, EquipmentType} */
    private function context(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id, 'trade_name' => 'Matriz original']);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'name' => 'Unidade original']);
        $customer = Customer::factory()->create(['phone' => '111111111']);
        $type = EquipmentType::factory()->create(['name' => 'Notebook']);
        $equipment = CustomerEquipment::factory()->create(['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id, 'serial_number' => 'ABC']);

        return [$tenant, $company, $branch, $customer, $equipment, $type];
    }
}
