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
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_lifecycle_updates_timestamps_and_history(): void
    {
        [$tenant, $data, $user] = $this->orderData('lifecycle');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $service = app(OrderStatusService::class);

        $service->transition($order, OrderStatus::IN_PROGRESS, $user);
        $service->transition($order->fresh(), OrderStatus::COMPLETED, $user, 'Reparo concluído');
        $service->transition($order->fresh(), OrderStatus::DELIVERED, $user, 'Entregue ao cliente');

        $order = $order->fresh();
        $this->assertSame(OrderStatus::DELIVERED, $order->status);
        $this->assertNotNull($order->started_at);
        $this->assertNotNull($order->completed_at);
        $this->assertNotNull($order->delivered_at);
        $this->assertSame(3, $order->statusHistory()->count());
        $this->assertSame(['in_progress', 'completed', 'delivered'], $order->statusHistory->pluck('to_status')->all());
        $this->assertSame($user->id, $order->statusHistory->last()->changed_by);
    }

    public function test_budget_flow_is_allowed(): void
    {
        [$tenant, $data, $user] = $this->orderData('budget-flow');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $service = app(OrderStatusService::class);

        $service->transition($order, OrderStatus::BUDGET_GENERATED, $user);
        $order = $service->transition($order, OrderStatus::BUDGET_APPROVED, $user);
        $order = $service->transition($order, OrderStatus::IN_PROGRESS, $user);

        $this->assertSame(OrderStatus::IN_PROGRESS, $order->status);
    }

    public function test_invalid_transition_does_not_change_order_or_create_history(): void
    {
        [$tenant, $data, $user] = $this->orderData('invalid-flow');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);

        $this->expectException(LogicException::class);
        app(OrderStatusService::class)->transition($order, OrderStatus::DELIVERED, $user);

        $this->assertSame(OrderStatus::OPEN, $order->fresh()->status);
        $this->assertSame(0, OrderStatusHistory::count());
    }

    public function test_status_cannot_be_changed_directly(): void
    {
        [$tenant, $data] = $this->orderData('direct-status');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $order->status = OrderStatus::IN_PROGRESS;

        $this->expectException(LogicException::class);
        $order->save();
    }

    public function test_cancellation_requires_note_and_sets_timestamp(): void
    {
        [$tenant, $data, $user] = $this->orderData('cancel-flow');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $service = app(OrderStatusService::class);

        $this->expectException(LogicException::class);
        $service->transition($order, OrderStatus::CANCELLED, $user);
    }

    public function test_cancelled_order_records_reason_and_cannot_continue(): void
    {
        [$tenant, $data, $user] = $this->orderData('cancelled-flow');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $service = app(OrderStatusService::class);

        $order = $service->transition($order, OrderStatus::CANCELLED, $user, 'Cliente desistiu');

        $this->assertSame(OrderStatus::CANCELLED, $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame('Cliente desistiu', $order->statusHistory->first()->note);
        $this->expectException(LogicException::class);
        $service->transition($order, OrderStatus::OPEN, $user);
    }

    public function test_cross_tenant_actor_is_rejected(): void
    {
        [$tenantA, $data, $userA] = $this->orderData('actor-a');
        [$tenantB, , $userB] = $this->orderData('actor-b');
        $tenantA->makeCurrent();
        $order = Order::factory()->create($data);
        $tenantB->makeCurrent();

        $this->expectException(LogicException::class);
        app(OrderStatusService::class)->transition($order, OrderStatus::IN_PROGRESS, $userB);
    }

    /** @return array{0: Tenant, 1: array<string, int>, 2: User} */
    private function orderData(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $equipment = CustomerEquipment::factory()->create(['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id]);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        return [$tenant, [
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'customer_equipment_id' => $equipment->id,
            'equipment_type_id' => $type->id,
            'created_by' => $user->id,
        ], $user];
    }
}
