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
        Schema::create('asset_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Dell Latitude 7420 Laptop"
            $table->string('manufacturer')->nullable(); // e.g., "Dell", "HP", "Apple"
            $table->string('model_name')->nullable(); // e.g., "Latitude 7420"
            $table->string('model_number')->nullable(); // e.g., "LAT7420-001"
            $table->foreignId('asset_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('specifications')->nullable(); // Flexible specs like RAM, CPU, Storage, etc.
            $table->text('default_notes')->nullable();
            $table->boolean('is_global')->default(false); // System-wide templates vs company-specific
            $table->integer('usage_count')->default(0); // Track popularity
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'asset_category_id']);
            $table->index(['is_global', 'usage_count']);
            $table->index(['manufacturer', 'model_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_templates');
    }
};
