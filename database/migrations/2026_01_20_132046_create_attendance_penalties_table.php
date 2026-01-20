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
        Schema::create('attendance_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('attendance_date');
            $table->integer('late_minutes');
            $table->string('violation_type');
            $table->tinyInteger('repeat_number');
            $table->string('action_type');
            $table->integer('action_value')->nullable();
            $table->string('action_text');
            $table->text('reason_text');
            $table->timestamps();
            
            // Unique constraint: one penalty per employee per date
            $table->unique(['employee_id', 'attendance_date'], 'attendance_penalties_unique');
            
            // Index for faster queries (shortened name for MySQL limit)
            $table->index(['employee_id', 'violation_type', 'attendance_date'], 'penalties_emp_viol_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_penalties');
    }
};
