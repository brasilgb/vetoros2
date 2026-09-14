<?php

namespace Tests\Feature\Multitenancy;

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
        $headquarters = Company::create(['trade_name' => 'Matriz A']);
        $branch = Company::create([
            'trade_name' => 'Filial A',
            'type' => 'branch',
            'parent_id' => $headquarters->id,
        ]);
        $access = app(CompanyAccessService::class);
        $access->grant($user, $headquarters, true);
        $access->grant($user, $branch);

        $response = $this->actingAs($user)
            ->post(route('companies.switch'), ['company_id' => $branch->id]);

        $response->assertRedirect();
        $response->assertSessionHas(CurrentCompany::SESSION_KEY, $branch->id);
    }

    public function test_user_cannot_switch_to_an_unauthorized_company(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);
        $headquarters = Company::create(['trade_name' => 'Matriz A']);
        $unauthorizedCompany = Company::create([
            'trade_name' => 'Filial A',
            'type' => 'branch',
            'parent_id' => $headquarters->id,
        ]);

        $this->actingAs($user)
            ->post(route('companies.switch'), ['company_id' => $unauthorizedCompany->id])
            ->assertForbidden();
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
