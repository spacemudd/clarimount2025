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
        Schema::create('custody_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->json('previous_state')->comment('JSON containing previous asset assignments');
            $table->json('new_state')->comment('JSON containing new asset assignments');
            $table->text('changes_summary')->nullable()->comment('Summary of changes made');
            $table->string('document_path')->nullable()->comment('Path to uploaded document/proof');
            $table->enum('status', ['pending', 'approved', 'signed', 'completed'])->default('pending');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index(['updated_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custody_changes');
    }
};
