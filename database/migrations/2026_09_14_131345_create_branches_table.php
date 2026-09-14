<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_headquarters')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['tenant_id', 'company_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
