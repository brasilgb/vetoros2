<?php

namespace Tests\Feature\CRM;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\EquipmentType;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_index_is_tenant_safe_and_searches_on_the_backend(): void
    {
        [$tenant, $user] = $this->context('customer-http-a');
        $tenant->makeCurrent();
        Customer::factory()->create(['name' => 'Cliente Visível']);
        $other = Tenant::factory()->create(['slug' => 'customer-http-b']);
        $other->makeCurrent();
        Customer::factory()->create(['name' => 'Cliente Oculto']);

        $tenant->makeCurrent();
        $response = $this->actingAs($user)->get('/customers?search=Visível');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('customers/index')->where('customers.data.0.name', 'Cliente Visível'));
    }

    public function test_customer_can_be_created_with_normalized_document(): void
    {
        [$tenant, $user] = $this->context('customer-http-c');
        $tenant->makeCurrent();
        $response = $this->actingAs($user)->post('/customers', ['type' => 'individual', 'name' => 'Maria', 'cpf' => '123.456.789-00']);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['tenant_id' => $tenant->id, 'name' => 'Maria', 'cpf' => '12345678900']);
    }

    public function test_customer_detail_can_create_equipment_and_open_order_contextually(): void
    {
        [$tenant, $user] = $this->context('customer-http-d');
        $tenant->makeCurrent();
        $customer = Customer::factory()->create();
        $type = EquipmentType::factory()->create(['name' => 'Notebook']);
        $response = $this->actingAs($user)->post("/customers/{$customer->id}/equipment", ['equipment_type_id' => $type->id, 'brand' => 'Dell', 'model' => 'Latitude']);

        $response->assertRedirect("/customers/{$customer->id}");
        $this->assertDatabaseHas('customer_equipments', ['tenant_id' => $tenant->id, 'customer_id' => $customer->id, 'equipment_type_id' => $type->id]);
    }

    /** @return array{0: Tenant, 1: User} */
    private function context(string $slug): array
    {
        $tenant = Tenant::factory()->create(['slug' => $slug]);
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::factory()->headquarters()->create(['tenant_id' => $tenant->id]);
        Branch::factory()->create(['tenant_id' => $tenant->id, 'company_id' => $company->id]);
        app(CompanyAccessService::class)->grant($user, $company, true);

        return [$tenant, $user];
    }
}
