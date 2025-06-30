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
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Category code like 'COMP', 'PRNT', 'PHON', etc.
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon name for UI
            $table->string('color')->nullable(); // Color code for UI
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->json('custom_fields')->nullable(); // For category-specific fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
