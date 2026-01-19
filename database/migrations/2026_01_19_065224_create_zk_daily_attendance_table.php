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
        Schema::create('zk_daily_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('zk_devices')->onDelete('cascade');
            $table->string('device_pin');
            $table->date('att_date');
            $table->dateTime('first_punch');
            $table->dateTime('last_punch');
            $table->tinyInteger('first_verify_mode')->nullable();
            $table->tinyInteger('last_verify_mode')->nullable();
            $table->integer('punch_count')->default(0);
            $table->timestamps();
            
            // Unique constraint: one record per device, employee, and date
            $table->unique(['device_id', 'device_pin', 'att_date'], 'zk_daily_attendance_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_daily_attendance');
    }
};
