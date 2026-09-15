<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'company_id', 'active']);
            $table->foreign(['tenant_id', 'company_id'])->references(['tenant_id', 'id'])->on('companies')->cascadeOnDelete();
        });

        $legacyBranchIds = [];

        DB::table('companies')
            ->where('type', 'branch')
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->each(function (object $company) use (&$legacyBranchIds): void {
                $branchId = DB::table('branches')->insertGetId([
                    'tenant_id' => $company->tenant_id,
                    'company_id' => $company->parent_id,
                    'name' => $company->trade_name,
                    'active' => $company->is_active,
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                ]);
                $legacyBranchIds[(int) $company->id] = $branchId;
            });

        Schema::create('branch_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'branch_id', 'user_id']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'user_id'])->references(['tenant_id', 'id'])->on('users')->cascadeOnDelete();
        });

        foreach ($legacyBranchIds as $legacyCompanyId => $branchId) {
            DB::table('company_user')
                ->where('company_id', $legacyCompanyId)
                ->orderBy('id')
                ->each(function (object $access) use ($branchId): void {
                    DB::table('branch_user')->insertOrIgnore([
                        'tenant_id' => $access->tenant_id,
                        'branch_id' => $branchId,
                        'user_id' => $access->user_id,
                        'created_at' => $access->created_at,
                        'updated_at' => $access->updated_at,
                    ]);
                });
        }

        Schema::table('orders', function (Blueprint $table): void {
            $table->foreignId('branch_id')->nullable()->after('company_id');
            $table->index(['tenant_id', 'branch_id', 'status']);
            $table->foreign(['tenant_id', 'branch_id'])->references(['tenant_id', 'id'])->on('branches')->restrictOnDelete();
        });

        Schema::create('order_assignment_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id', 'changed_at']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'from_user_id'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
            $table->foreign(['tenant_id', 'to_user_id'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
            $table->foreign(['tenant_id', 'changed_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_assignment_history');
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['tenant_id', 'branch_id']);
            $table->dropIndex(['tenant_id', 'branch_id', 'status']);
            $table->dropColumn('branch_id');
        });
        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('branches');
    }
};
