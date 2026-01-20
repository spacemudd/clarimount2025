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
        Schema::create('labor_law_rules', function (Blueprint $table) {
            $table->id();
            $table->string('violation_type');
            $table->integer('min_minutes')->nullable();
            $table->integer('max_minutes')->nullable();
            $table->tinyInteger('repeat_number'); // 1, 2, 3, 4
            $table->string('action_type'); // warning, deduction_percentage, deduction_days, termination
            $table->integer('action_value')->nullable();
            $table->text('reason_text');
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['violation_type', 'repeat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_law_rules');
    }
};
