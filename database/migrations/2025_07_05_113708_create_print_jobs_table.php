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
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique(); // Unique identifier for the print job
            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who requested the print
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('printer_name')->nullable(); // Target printer name
            $table->json('print_data'); // Asset data for printing (asset_tag, company_name, etc.)
            $table->text('error_message')->nullable(); // Error message if print failed
            $table->timestamp('requested_at'); // When the print was requested
            $table->timestamp('processed_at')->nullable(); // When the print was processed
            $table->timestamp('completed_at')->nullable(); // When the print was completed
            $table->string('print_station_id')->nullable(); // ID of the print station that processed this
            $table->json('metadata')->nullable(); // Additional metadata (device info, etc.)
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['status', 'priority']);
            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
