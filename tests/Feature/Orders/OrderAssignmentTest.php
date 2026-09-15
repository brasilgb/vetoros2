<?php

namespace Tests\Feature\Orders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\OrderAssignmentHistory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_records_null_to_a_to_b_to_null_without_changing_status(): void
    {
        [$tenant, $company, $branch, $order] = $this->context('assignment');
        $actor = User::factory()->create(['tenant_id' => $tenant->id]);
        $first = User::factory()->create(['tenant_id' => $tenant->id]);
        $second = User::factory()->create(['tenant_id' => $tenant->id]);
        $company->users()->attach([$actor->id, $first->id, $second->id], ['tenant_id' => $tenant->id]);
        $tenant->makeCurrent();
        $service = app(OrderAssignmentService::class);

        $service->assign($order, $first, $actor, 'first');
        $service->reassign($order, $second, $actor, 'second');
        $service->unassign($order, $actor, 'clear');

        $this->assertNull($order->fresh()->assigned_to);
        $this->assertSame(['first', 'second', 'clear'], OrderAssignmentHistory::where('order_id', $order->id)->orderBy('id')->pluck('note')->all());
        $this->assertSame(3, OrderAssignmentHistory::where('order_id', $order->id)->count());
    }

    public function test_assignment_rejects_technician_from_another_tenant_or_without_access(): void
    {
        [$tenant, $company, $branch, $order] = $this->context('assignment-a');
        [$otherTenant] = $this->context('assignment-b');
        $actor = User::factory()->create(['tenant_id' => $tenant->id]);
        $foreign = User::factory()->create(['tenant_id' => $otherTenant->id]);
        $tenant->makeCurrent();
        $company->users()->attach($actor->id, ['tenant_id' => $tenant->id]);

        $this->expectException(LogicException::class);
        app(OrderAssignmentService::class)->assign($order, $foreign, $actor);
    }

    public function test_assignment_history_is_append_only(): void
    {
        [$tenant, $company, $branch, $order] = $this->context('immutable');
        $actor = User::factory()->create(['tenant_id' => $tenant->id]);
        $tenant->makeCurrent();
        $company->users()->attach($actor->id, ['tenant_id' => $tenant->id]);
        $history = OrderAssignmentHistory::create(['order_id' => $order->id, 'changed_by' => $actor->id, 'changed_at' => now()]);

        $this->expectException(LogicException::class);
        $history->update(['note' => 'mutated']);
    }

    /** @return array{Tenant, Company, Branch, Order} */
    private function context(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        $branch = Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        $customer = Customer::factory()->create(['tenant_id' => $tenant->id]);
        $type = EquipmentType::factory()->create(['tenant_id' => $tenant->id]);
        $order = Order::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id, 'branch_id' => $branch->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id]);

        return [$tenant, $company, $branch, $order];
    }
}
