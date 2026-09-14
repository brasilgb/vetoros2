<?php

namespace Tests\Feature\Multitenancy;

use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CompanyHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_can_belong_to_an_active_headquarters_in_the_same_tenant(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $headquarters = Company::create(['trade_name' => 'Matriz A']);

        $branch = Company::factory()->branch($headquarters)->create([
            'trade_name' => 'Filial A',
        ]);

        $this->assertSame($headquarters->id, $branch->parent_id);
        $this->assertCount(1, $headquarters->branches);
    }

    public function test_headquarters_cannot_have_a_parent(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $headquarters = Company::create(['trade_name' => 'Matriz A']);

        $this->expectException(LogicException::class);

        Company::create([
            'trade_name' => 'Matriz inválida',
            'parent_id' => $headquarters->id,
        ]);
    }

    public function test_branch_cannot_point_to_another_branch(): void
    {
        $tenant = $this->createTenant('Tenant A', 'tenant-a');
        $tenant->makeCurrent();
        $headquarters = Company::create(['trade_name' => 'Matriz A']);
        $branch = Company::create([
            'trade_name' => 'Filial A',
            'type' => CompanyType::BRANCH,
            'parent_id' => $headquarters->id,
        ]);

        $this->expectException(LogicException::class);

        Company::create([
            'trade_name' => 'Filial de filial',
            'type' => CompanyType::BRANCH,
            'parent_id' => $branch->id,
        ]);
    }

    private function createTenant(string $name, string $slug): Tenant
    {
        return Tenant::create(['name' => $name, 'slug' => $slug, 'active' => true]);
    }
}
