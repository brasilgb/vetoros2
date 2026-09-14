<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            return;
        }

        $companyIds = [];

        DB::table('branches')->orderBy('id')->each(function (object $branch) use (&$companyIds): void {
            $companyId = DB::table('companies')->insertGetId([
                'tenant_id' => $branch->tenant_id,
                'parent_id' => $branch->company_id,
                'type' => 'branch',
                'legal_name' => null,
                'trade_name' => $branch->name,
                'cnpj' => null,
                'state_registration' => null,
                'email' => null,
                'phone' => null,
                'whatsapp' => null,
                'zip_code' => null,
                'street' => null,
                'number' => null,
                'complement' => null,
                'district' => null,
                'city' => null,
                'state' => null,
                'is_active' => $branch->active,
                'created_at' => $branch->created_at,
                'updated_at' => $branch->updated_at,
            ]);

            $companyIds[$branch->id] = $companyId;
        });

        if (Schema::hasTable('branch_user') && Schema::hasTable('company_user')) {
            DB::table('branch_user')->orderBy('id')->each(function (object $access) use ($companyIds): void {
                $companyId = $companyIds[$access->branch_id] ?? null;

                if (! $companyId) {
                    return;
                }

                DB::table('company_user')->insertOrIgnore([
                    'tenant_id' => $access->tenant_id,
                    'company_id' => $companyId,
                    'user_id' => $access->user_id,
                    'is_default' => false,
                    'created_at' => $access->created_at,
                    'updated_at' => $access->updated_at,
                ]);
            });
        }

        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('branches');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new RuntimeException('The branch consolidation migration is irreversible because branch records are now companies.');
    }
};
