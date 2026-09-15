<?php

namespace Database\Factories;

use App\Enums\CompanyType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Branch $branch): void {
            $company = Company::withoutGlobalScopes()->find($branch->company_id);
            $tenantId = $branch->tenant_id ?? Tenant::current()?->getKey();

            if ($company?->tenant_id !== $tenantId || $company?->type !== CompanyType::HEADQUARTERS) {
                $branch->company_id = Company::factory()
                    ->headquarters()
                    ->create(['tenant_id' => $tenantId])
                    ->getKey();
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory()->headquarters(),
            'name' => fake()->company(),
            'active' => true,
        ];
    }
}
