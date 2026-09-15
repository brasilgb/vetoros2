<?php

namespace Tests\Feature\Multitenancy;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyAccessService;
use App\Support\CurrentCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyContextHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_company_switch_updates_the_session(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $headquarters = Company::create(['tenant_id' => $tenant->id, 'trade_name' => 'Matriz A']);
        $branch = Branch::factory()->create(['company_id' => $headquarters->id, 'name' => 'Filial A']);
        $access = app(CompanyAccessService::class);
        $access->grant($user, $headquarters, true);
        $branch->users()->attach($user->id, ['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)
            ->post(route('companies.switch'), ['company_id' => $headquarters->id]);

        $response->assertRedirect();
        $response->assertSessionHas(CurrentCompany::SESSION_KEY, $headquarters->id);
    }

    public function test_user_cannot_switch_to_an_unauthorized_company(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $headquarters = Company::create(['tenant_id' => $tenant->id, 'trade_name' => 'Matriz A']);
        $otherTenant = $this->createTenant('Tenant B', 'tenant-b');
        $otherTenant->makeCurrent();
        $unauthorizedCompany = Company::factory()->headquarters()->create(['tenant_id' => $otherTenant->id, 'trade_name' => 'Empresa não autorizada']);
        $tenant->makeCurrent();

        $this->actingAs($user)
            ->post(route('companies.switch'), ['company_id' => $unauthorizedCompany->id])
            ->assertNotFound();
    }

    public function test_dashboard_requires_an_authorized_current_company(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    private function createTenant(string $name, string $slug): Tenant
    {
        return Tenant::create(['name' => $name, 'slug' => $slug, 'active' => true]);
    }
}
