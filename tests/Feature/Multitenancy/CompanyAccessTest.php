<?php

namespace Tests\Feature\Multitenancy;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyAccessService;
use App\Support\CurrentCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CompanyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_matrix_and_filials_with_one_default_company(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $headquarters = Company::create(['tenant_id' => $tenant->id, 'trade_name' => 'Matriz A']);
        $branch = Branch::factory()->create(['company_id' => $headquarters->id, 'name' => 'Filial A']);

        $service = app(CompanyAccessService::class);
        $service->grant($user, $headquarters, true);
        $branch->users()->attach($user->id, ['tenant_id' => $tenant->id]);

        $this->assertDatabaseHas('company_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'company_id' => $headquarters->id,
            'is_default' => true,
        ]);
        $this->assertCount(1, $user->fresh()->companies);
        $this->assertCount(1, $user->fresh()->branches);
        $this->assertSame($headquarters->id, $user->fresh()->defaultCompany()?->id);
    }

    public function test_user_cannot_receive_access_to_a_company_from_another_tenant(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');
        $tenantA->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenantA->id]);
        $tenantB->makeCurrent();
        $company = Company::create(['tenant_id' => $tenantB->id, 'trade_name' => 'Empresa B']);
        $tenantA->makeCurrent();

        $this->expectException(LogicException::class);

        app(CompanyAccessService::class)->grant($user, $company);
    }

    public function test_user_can_switch_only_to_an_active_authorized_company(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $company = Company::create(['tenant_id' => $tenant->id, 'trade_name' => 'Empresa A']);
        app(CompanyAccessService::class)->grant($user, $company);

        app(CurrentCompany::class)->set($user, $company);

        $this->assertSame($company->id, app(CurrentCompany::class)->get($user)?->id);
    }

    private function createTenant(string $name, string $slug): Tenant
    {
        return Tenant::create(['name' => $name, 'slug' => $slug, 'active' => true]);
    }
}
