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
        Schema::create('zk_attendance_raw', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('zk_devices')->onDelete('cascade');
            $table->string('device_pin');
            $table->dateTime('punch_time');
            $table->tinyInteger('verify_mode')->nullable();
            $table->text('raw_line');
            $table->dateTime('received_at');
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['device_id', 'device_pin', 'punch_time']);
            // Unique constraint to prevent duplicate records
            $table->unique(['device_id', 'device_pin', 'punch_time'], 'zk_attendance_raw_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zk_attendance_raw');
    }
};
