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
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_tag'); // Sequential number like '1', '2', '3' per company
            $table->text('description')->nullable();
            $table->foreignId('asset_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Hardware details
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('specifications')->nullable();
            
            // Purchase & Financial
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('vendor')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('warranty_expires')->nullable();
            
            // Status & Assignment
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired', 'disposed'])->default('available');
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->onDelete('set null');
            $table->date('assigned_date')->nullable();
            
            // Maintenance
            $table->date('last_maintenance')->nullable();
            $table->date('next_maintenance')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('custom_data')->nullable();
            $table->timestamps();
            
            // Ensure asset_tag is unique within each company
            $table->unique(['asset_tag', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
