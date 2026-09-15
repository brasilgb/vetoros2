<?php

namespace Tests\Feature\Orders;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_is_isolated_by_tenant(): void
    {
        [$tenantA, $dataA] = $this->orderData('orders-a');
        [$tenantB] = $this->orderData('orders-b');

        $tenantA->makeCurrent();
        $order = Order::factory()->create($dataA);

        $tenantB->makeCurrent();

        $this->assertNull(Order::find($order->id));
    }

    public function test_order_requires_a_company_from_the_current_tenant(): void
    {
        [$tenantA, $dataA] = $this->orderData('orders-a');
        [, $dataB] = $this->orderData('orders-b');

        $tenantA->makeCurrent();
        $this->expectException(LogicException::class);
        Order::factory()->create([...$dataA, 'company_id' => $dataB['company_id']]);
    }

    public function test_customer_equipment_must_belong_to_the_order_customer(): void
    {
        [$tenant, $data] = $this->orderData('orders');
        $tenant->makeCurrent();
        $otherCustomer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $otherEquipment = CustomerEquipment::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $otherCustomer->id,
            'equipment_type_id' => $data['equipment_type_id'],
        ]);

        $this->expectException(LogicException::class);
        Order::factory()->create([...$data, 'customer_equipment_id' => $otherEquipment->id]);
    }

    public function test_order_number_is_unique_per_tenant(): void
    {
        [$tenant, $data] = $this->orderData('orders');
        $tenant->makeCurrent();
        Order::factory()->create([...$data, 'order_number' => 1001]);

        $this->expectException(UniqueConstraintViolationException::class);
        Order::factory()->create([...$data, 'order_number' => 1001]);
    }

    public function test_same_order_number_can_exist_in_different_tenants(): void
    {
        [$tenantA, $dataA] = $this->orderData('orders-a');
        [$tenantB, $dataB] = $this->orderData('orders-b');

        $tenantA->makeCurrent();
        Order::factory()->create([...$dataA, 'order_number' => 1001]);
        $tenantB->makeCurrent();
        $order = Order::factory()->create([...$dataB, 'order_number' => 1001]);

        $this->assertSame(1001, $order->order_number);
    }

    public function test_order_exposes_its_operational_relationships(): void
    {
        [$tenant, $data] = $this->orderData('orders');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $history = OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'to_status' => OrderStatus::OPEN,
        ]);

        $order->load(['company', 'customer', 'customerEquipment', 'equipmentType', 'statusHistory']);

        $this->assertSame($data['company_id'], $order->company->id);
        $this->assertSame($data['customer_id'], $order->customer->id);
        $this->assertSame($data['customer_equipment_id'], $order->customerEquipment->id);
        $this->assertSame($data['equipment_type_id'], $order->equipmentType->id);
        $this->assertSame($history->id, $order->statusHistory->first()->id);
    }

    public function test_status_history_is_tenant_scoped_and_ordered(): void
    {
        [$tenant, $data] = $this->orderData('orders');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $older = OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'changed_at' => now()->subMinute(),
        ]);
        $newer = OrderStatusHistory::factory()->create([
            'order_id' => $order->id,
            'changed_at' => now(),
        ]);

        $this->assertSame([$older->id, $newer->id], $order->fresh()->statusHistory->pluck('id')->all());
    }

    public function test_status_history_rejects_an_order_from_another_tenant(): void
    {
        [$tenantA, $dataA] = $this->orderData('orders-a');
        [$tenantB, $dataB] = $this->orderData('orders-b');
        $tenantB->makeCurrent();
        $orderB = Order::factory()->create($dataB);

        $tenantA->makeCurrent();
        $this->expectException(LogicException::class);
        OrderStatusHistory::factory()->create(['order_id' => $orderB->id]);
    }

    public function test_order_accepts_nullable_customer_equipment(): void
    {
        [$tenant, $data] = $this->orderData('orders');
        $tenant->makeCurrent();
        $order = Order::factory()->create([...$data, 'customer_equipment_id' => null]);

        $this->assertNull($order->customer_equipment_id);
    }

    /** @return array{0: Tenant, 1: array<string, int>} */
    private function orderData(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $equipment = CustomerEquipment::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'equipment_type_id' => $type->id,
        ]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        return [$tenant, [
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'customer_equipment_id' => $equipment->id,
            'equipment_type_id' => $type->id,
            'created_by' => $user->id,
        ]];
    }
}
