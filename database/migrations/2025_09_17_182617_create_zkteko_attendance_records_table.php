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
        Schema::create('zkteko_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('zkteko_devices')->onDelete('cascade');
            $table->string('employee_id')->nullable();
            $table->timestamp('timestamp');
            $table->string('type')->nullable(); // check_in, check_out, break_in, break_out
            $table->json('data')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'timestamp']);
            $table->index(['employee_id', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteko_attendance_records');
    }
};
