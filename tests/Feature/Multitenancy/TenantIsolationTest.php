<?php

namespace Tests\Feature\Multitenancy;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_receives_current_tenant_automatically(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');

        $tenant->makeCurrent();

        $company = Company::create([
            'trade_name' => 'Empresa A',
        ]);

        $this->assertSame(
            $tenant->getKey(),
            $company->tenant_id
        );
    }

    public function test_tenant_cannot_see_company_from_another_tenant(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');

        $tenantA->makeCurrent();

        $companyA = Company::create([
            'trade_name' => 'Empresa A',
        ]);

        $tenantB->makeCurrent();

        $companyB = Company::create([
            'trade_name' => 'Empresa B',
        ]);

        $tenantA->makeCurrent();

        $this->assertNotNull(
            Company::find($companyA->id)
        );

        $this->assertNull(
            Company::find($companyB->id)
        );
    }

    public function test_each_tenant_only_lists_its_own_companies(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');

        $tenantA->makeCurrent();

        Company::create([
            'trade_name' => 'Empresa A',
        ]);

        $tenantB->makeCurrent();

        Company::create([
            'trade_name' => 'Empresa B',
        ]);

        $tenantA->makeCurrent();

        $this->assertSame(
            ['Empresa A'],
            Company::pluck('trade_name')->all()
        );

        $tenantB->makeCurrent();

        $this->assertSame(
            ['Empresa B'],
            Company::pluck('trade_name')->all()
        );
    }

    public function test_cannot_create_company_for_another_tenant(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');

        $tenantA->makeCurrent();

        $this->expectException(LogicException::class);

        $company = new Company([
            'trade_name' => 'Empresa Inválida',
        ]);

        $company->tenant_id = $tenantB->id;

        $company->save();
    }

    public function test_external_tenant_id_is_not_mass_assignable(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');
        $tenantA->makeCurrent();

        $company = Company::create([
            'tenant_id' => $tenantB->id,
            'trade_name' => 'Empresa A',
        ]);

        $this->assertSame($tenantA->id, $company->tenant_id);
    }

    public function test_company_type_and_parent_are_validated(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();

        $this->expectException(LogicException::class);

        Company::create([
            'trade_name' => 'Filial sem matriz',
            'type' => CompanyType::BRANCH,
        ]);
    }

    public function test_tenant_can_have_only_one_headquarters(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();

        Company::create(['trade_name' => 'Matriz A']);

        $this->expectException(LogicException::class);

        Company::create(['trade_name' => 'Matriz duplicada']);
    }

    private function createTenant(string $name, string $slug): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'active' => true,
        ]);
    }
}
