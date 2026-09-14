<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique([
                'tenant_id',
                'company_id',
                'user_id',
            ]);

            $table->foreign(['tenant_id', 'company_id'])
                ->references(['tenant_id', 'id'])
                ->on('companies')
                ->cascadeOnDelete();

            $table->foreign(['tenant_id', 'user_id'])
                ->references(['tenant_id', 'id'])
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
