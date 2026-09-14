<?php

namespace Tests\Feature\Multitenancy;

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSecurityHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_gets_current_tenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'active' => true,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/tenant-security-test');

        $response->assertOk();
    }

    public function test_user_without_tenant_is_forbidden(): void
    {
        $user = User::factory()->create([
            'tenant_id' => null,
        ]);

        $this
            ->actingAs($user)
            ->get('/tenant-security-test')
            ->assertForbidden();
    }

    public function test_inactive_tenant_is_forbidden(): void
    {
        $tenant = Tenant::create([
            'name' => 'Tenant Inativo',
            'slug' => 'tenant-inativo',
            'active' => false,
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this
            ->actingAs($user)
            ->get('/tenant-security-test')
            ->assertForbidden();
    }

    public function test_user_cannot_receive_company_access_from_another_tenant(): void
    {
        $tenantA = $this->createTenant('Tenant A', 'tenant-a');
        $tenantB = $this->createTenant('Tenant B', 'tenant-b');

        $tenantA->makeCurrent();

        $user = User::factory()->create([
            'tenant_id' => $tenantA->id,
        ]);

        $tenantB->makeCurrent();

        $companyB = Company::create([
            'trade_name' => 'Empresa B',
        ]);

        $tenantA->makeCurrent();

        $this->expectException(\LogicException::class);

        app(CompanyAccessService::class)->grant($user, $companyB);
    }

    private function createTenant(
        string $name,
        string $slug
    ): Tenant {
        return Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'active' => true,
        ]);
    }
}
