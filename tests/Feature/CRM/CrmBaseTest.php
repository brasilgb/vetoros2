<?php

namespace Tests\Feature\CRM;

use App\Models\ChecklistTemplate;
use App\Models\Customer;
use App\Models\CustomerEquipment;
use App\Models\EquipmentType;
use App\Models\Tenant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CrmBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_normalizes_individual_and_company_documents(): void
    {
        $tenant = $this->tenant('crm');
        $tenant->makeCurrent();

        $individual = Customer::factory()->create(['cpf' => '123.456.789-00']);
        $company = Customer::factory()->create([
            'type' => 'company',
            'cnpj' => '12.345.678/0001-90',
        ]);

        $this->assertSame('12345678900', $individual->cpf);
        $this->assertSame('12345678000190', $company->cnpj);
    }

    public function test_customer_number_is_unique_within_a_tenant(): void
    {
        $tenant = $this->tenant('crm-a');

        $tenant->makeCurrent();
        Customer::factory()->create(['customer_number' => 7]);

        $this->expectException(UniqueConstraintViolationException::class);
        Customer::factory()->create(['customer_number' => 7]);
    }

    public function test_customer_number_can_exist_in_different_tenants(): void
    {
        $tenantA = $this->tenant('crm-a');
        $tenantB = $this->tenant('crm-b');

        $tenantA->makeCurrent();
        Customer::factory()->create(['customer_number' => 7]);
        $tenantB->makeCurrent();
        Customer::factory()->create(['customer_number' => 7]);
        $this->assertSame(1, Customer::count());
    }

    public function test_tenant_scope_isolates_all_crm_entities(): void
    {
        $tenantA = $this->tenant('crm-a');
        $tenantB = $this->tenant('crm-b');

        $tenantA->makeCurrent();
        Customer::factory()->create(['name' => 'Cliente A']);
        $typeA = EquipmentType::factory()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Tipo A',
        ]);

        ChecklistTemplate::factory()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Checklist A',
            'equipment_type_id' => $typeA->id,
        ]);

        $tenantB->makeCurrent();
        Customer::factory()->create(['name' => 'Cliente B']);
        $typeB = EquipmentType::factory()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Tipo B',
        ]);

        ChecklistTemplate::factory()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Checklist B',
            'equipment_type_id' => $typeB->id,
        ]);

        $this->assertSame(['Cliente B'], Customer::pluck('name')->all());
        $this->assertSame(['Tipo B'], EquipmentType::pluck('name')->all());
        $this->assertSame(['Checklist B'], ChecklistTemplate::pluck('name')->all());
    }

    public function test_customer_equipment_requires_same_tenant_relations(): void
    {
        $tenantA = $this->tenant('crm-a');
        $tenantB = $this->tenant('crm-b');

        $tenantA->makeCurrent();
        $customerA = Customer::factory()->create(['tenant_id' => $tenantA->id]);
        $typeA = EquipmentType::factory()->create(['tenant_id' => $tenantA->id]);

        $tenantB->makeCurrent();
        $customerB = Customer::factory()->create(['tenant_id' => $tenantB->id]);
        $typeB = EquipmentType::factory()->create(['tenant_id' => $tenantB->id]);

        $tenantA->makeCurrent();
        $this->expectException(LogicException::class);
        CustomerEquipment::factory()->create([
            'customer_id' => $customerB->id,
            'equipment_type_id' => $typeA->id,
        ]);
    }

    public function test_checklist_template_rejects_equipment_type_from_another_tenant(): void
    {
        $tenantA = $this->tenant('crm-a');
        $tenantB = $this->tenant('crm-b');

        $tenantB->makeCurrent();
        $typeB = EquipmentType::factory()->create(['tenant_id' => $tenantB->id]);

        $tenantA->makeCurrent();
        $this->expectException(LogicException::class);
        ChecklistTemplate::factory()->create(['equipment_type_id' => $typeB->id]);
    }

    public function test_checklist_items_are_ordered_by_sort_order(): void
    {
        $tenant = $this->tenant('crm');
        $tenant->makeCurrent();
        $template = ChecklistTemplate::factory()->create(['equipment_type_id' => null]);

        $template->items()->createMany([
            ['description' => 'Segundo', 'sort_order' => 2],
            ['description' => 'Primeiro', 'sort_order' => 1],
        ]);

        $this->assertSame(['Primeiro', 'Segundo'], $template->fresh()->items->pluck('description')->all());
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::factory()->create(['slug' => $slug]);
    }
}
