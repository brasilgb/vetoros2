<?php

namespace Tests\Feature\Orders;

use App\Models\Branch;
use App\Models\ChecklistTemplate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Order;
use App\Models\OrderChecklist;
use App\Models\OrderEquipmentAccessory;
use App\Models\OrderEquipmentCondition;
use App\Models\OrderMedia;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderReceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_can_have_multiple_accessories_and_conditions(): void
    {
        [$tenant, $data] = $this->orderData('reception');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);

        $order->equipmentAccessories()->createMany([
            ['name' => 'Carregador', 'quantity' => 1],
            ['name' => 'Bolsa', 'quantity' => 1],
        ]);
        $order->equipmentConditions()->createMany([
            ['description' => 'Tampa riscada', 'severity' => 'minor'],
            ['description' => 'Parafuso ausente', 'severity' => 'moderate'],
        ]);

        $this->assertCount(2, $order->equipmentAccessories);
        $this->assertCount(2, $order->equipmentConditions);
    }

    public function test_reception_records_are_isolated_between_tenants(): void
    {
        [$tenantA, $dataA] = $this->orderData('reception-a');
        [$tenantB, $dataB] = $this->orderData('reception-b');
        $tenantA->makeCurrent();
        $order = Order::factory()->create($dataA);
        OrderEquipmentAccessory::factory()->create(['order_id' => $order->id]);

        $tenantB->makeCurrent();
        $otherOrder = Order::factory()->create($dataB);

        $this->assertCount(0, OrderEquipmentAccessory::all());
        $this->assertCount(0, $otherOrder->equipmentAccessories);
    }

    public function test_checklist_application_materializes_template_items(): void
    {
        [$tenant, $data] = $this->orderData('checklist');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $template = ChecklistTemplate::factory()->create([
            'equipment_type_id' => $data['equipment_type_id'],
            'name' => 'Checklist de notebook',
            'type' => 'entry',
        ]);
        $template->items()->createMany([
            ['description' => 'Liga?', 'input_type' => 'boolean', 'sort_order' => 1],
            ['description' => 'Tela íntegra?', 'input_type' => 'boolean', 'sort_order' => 2],
        ]);

        $checklist = OrderChecklist::createFromTemplate($order, $template);

        $this->assertSame('Checklist de notebook', $checklist->name);
        $this->assertSame(['Liga?', 'Tela íntegra?'], $checklist->items->pluck('label')->all());

        $template->items()->first()->update(['description' => 'Liga depois da alteração?']);

        $this->assertSame('Liga?', $checklist->fresh()->items->first()->label);
    }

    public function test_checklist_template_from_another_tenant_is_rejected(): void
    {
        [$tenantA, $dataA] = $this->orderData('checklist-a');
        [$tenantB, $dataB] = $this->orderData('checklist-b');
        $tenantB->makeCurrent();
        $orderB = Order::factory()->create($dataB);
        $templateB = ChecklistTemplate::factory()->create();

        $tenantA->makeCurrent();
        $orderA = Order::factory()->create($dataA);
        $this->expectException(LogicException::class);
        OrderChecklist::createFromTemplate($orderA, $templateB);
        unset($orderB);
    }

    public function test_reception_children_reject_orders_from_another_tenant(): void
    {
        [$tenantA, $dataA] = $this->orderData('children-a');
        [$tenantB, $dataB] = $this->orderData('children-b');
        $tenantB->makeCurrent();
        $orderB = Order::factory()->create($dataB);

        $tenantA->makeCurrent();
        $this->expectException(LogicException::class);
        OrderEquipmentCondition::factory()->create(['order_id' => $orderB->id]);
    }

    public function test_order_media_is_available_for_legacy_operational_images(): void
    {
        [$tenant, $data] = $this->orderData('media');
        $tenant->makeCurrent();
        $order = Order::factory()->create($data);
        $media = OrderMedia::factory()->create(['order_id' => $order->id, 'type' => 'entry']);

        $this->assertSame($order->id, $media->order->id);
        $this->assertSame('orders/example/image.jpg', $media->path);
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

        return [$tenant, [
            'tenant_id' => $tenant->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'customer_equipment_id' => $equipment->id,
            'equipment_type_id' => $type->id,
        ]];
    }
}
