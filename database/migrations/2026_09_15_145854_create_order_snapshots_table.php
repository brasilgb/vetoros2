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
        Schema::create('order_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id');
            $table->json('customer_data');
            $table->json('equipment_data')->nullable();
            $table->json('company_data');
            $table->json('branch_data');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tenant_id', 'order_id']);
            $table->unique(['tenant_id', 'id']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_snapshots');
    }
};
