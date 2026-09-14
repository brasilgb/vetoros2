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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('type', 20)->default('headquarters');

            $table->unsignedBigInteger('headquarters_tenant_id')
                ->nullable()
                ->storedAs("IF(type = 'headquarters', tenant_id, NULL)");

            $table->string('legal_name')->nullable();
            $table->string('trade_name');

            $table->string('cnpj', 14)->nullable();
            $table->string('state_registration')->nullable();

            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            $table->string('zip_code', 8)->nullable();
            $table->string('street')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->char('state', 2)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'parent_id']);

            $table->unique(['tenant_id', 'cnpj']);
            $table->unique(['tenant_id', 'id']);
            $table->unique('headquarters_tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
