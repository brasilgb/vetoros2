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
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedInteger('customer_number');
            $table->string('type', 20)->default('individual');
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('trade_name')->nullable();
            $table->string('cpf', 11)->nullable();
            $table->string('cnpj', 14)->nullable();
            $table->string('state_registration')->nullable();
            $table->string('municipal_registration')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->string('zip_code', 8)->nullable();
            $table->char('state', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('street')->nullable();
            $table->string('number', 50)->nullable();
            $table->string('complement')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'customer_number']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'cpf']);
            $table->index(['tenant_id', 'cnpj']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'whatsapp']);
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('equipment_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedInteger('equipment_type_number');
            $table->string('name');
            $table->boolean('uses_chart')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'equipment_type_number']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'name']);
        });

        Schema::create('customer_equipments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->restrictOnDelete();
            $table->unsignedInteger('equipment_number');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('imei')->nullable();
            $table->string('asset_tag')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'equipment_number']);
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'serial_number']);
            $table->index(['tenant_id', 'imei']);
            $table->index(['tenant_id', 'asset_tag']);
            $table->foreign(['tenant_id', 'customer_id'])->references(['tenant_id', 'id'])->on('customers')->cascadeOnDelete();
            $table->foreign(['tenant_id', 'equipment_type_id'])->references(['tenant_id', 'id'])->on('equipment_types')->restrictOnDelete();
        });

        Schema::create('checklist_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('equipment_type_id')->nullable()->constrained('equipment_types')->restrictOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('entry');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'id']);
            $table->index(['tenant_id', 'type', 'is_active']);
            $table->foreign(['tenant_id', 'equipment_type_id'])->references(['tenant_id', 'id'])->on('equipment_types')->restrictOnDelete();
        });

        Schema::create('checklist_template_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained('checklist_templates')->cascadeOnDelete();
            $table->string('description');
            $table->string('input_type', 30)->default('boolean');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['checklist_template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_template_items');
        Schema::dropIfExists('checklist_templates');
        Schema::dropIfExists('customer_equipments');
        Schema::dropIfExists('equipment_types');
        Schema::dropIfExists('customers');
    }
};
