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
        Schema::create('order_equipment_accessories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
        });

        Schema::create('order_equipment_conditions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('description');
            $table->string('severity', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
        });

        Schema::create('order_checklists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('checklist_template_id')->nullable()->constrained('checklist_templates')->restrictOnDelete();
            $table->string('type', 30);
            $table->string('name');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'checklist_template_id'])->references(['tenant_id', 'id'])->on('checklist_templates')->restrictOnDelete();
            $table->foreign(['tenant_id', 'created_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
            $table->foreign(['tenant_id', 'completed_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
        });

        Schema::create('order_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_checklist_id')->constrained('order_checklists')->cascadeOnDelete();
            $table->string('label');
            $table->string('input_type', 30);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('value_text')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->decimal('value_number', 12, 3)->nullable();
            $table->date('value_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(
                ['tenant_id', 'order_checklist_id', 'sort_order'],
                'oci_tenant_checklist_sort_idx'
            );
            $table->foreign(['tenant_id', 'order_checklist_id'])->references(['tenant_id', 'id'])->on('order_checklists')->cascadeOnDelete();
        });

        Schema::create('order_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('type', 30)->default('entry');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id', 'type']);
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'uploaded_by'])->references(['tenant_id', 'id'])->on('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_media');
        Schema::dropIfExists('order_checklist_items');
        Schema::dropIfExists('order_checklists');
        Schema::dropIfExists('order_equipment_conditions');
        Schema::dropIfExists('order_equipment_accessories');
    }
};
