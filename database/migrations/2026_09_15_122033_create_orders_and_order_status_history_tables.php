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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->unsignedInteger('order_number');
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('customer_equipment_id')->nullable()->constrained('customer_equipments')->restrictOnDelete();
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->restrictOnDelete();
            $table->string('status', 30)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->text('reported_issue');
            $table->text('technical_diagnosis')->nullable();
            $table->text('solution')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'order_number']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'company_id', 'status']);
            $table->index(['tenant_id', 'customer_id', 'created_at']);
            $table->index(['tenant_id', 'assigned_to', 'status']);
            $table->foreign(['tenant_id', 'company_id'])->references(['tenant_id', 'id'])->on('companies')->restrictOnDelete();
            $table->foreign(['tenant_id', 'customer_id'])->references(['tenant_id', 'id'])->on('customers')->restrictOnDelete();
            $table->foreign(['tenant_id', 'customer_equipment_id'])->references(['tenant_id', 'id'])->on('customer_equipments')->restrictOnDelete();
            $table->foreign(['tenant_id', 'equipment_type_id'])->references(['tenant_id', 'id'])->on('equipment_types')->restrictOnDelete();
            $table->foreign(['tenant_id', 'created_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
            $table->foreign(['tenant_id', 'assigned_to'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
        });

        Schema::create('order_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('changed_at');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id', 'changed_at']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'changed_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('orders');
    }
};
