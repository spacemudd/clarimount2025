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
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique(); // Category code like 'HW', 'SW', 'NET', etc.
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon name for UI
            $table->string('color')->nullable(); // Color code for UI
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->integer('default_priority')->default(2); // 1=High, 2=Medium, 3=Low
            $table->integer('sla_hours')->nullable(); // SLA response time in hours
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
