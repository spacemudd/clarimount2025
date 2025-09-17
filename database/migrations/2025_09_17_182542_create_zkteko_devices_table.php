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
        Schema::create('zkteko_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->string('device_name')->nullable();
            $table->string('model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->json('device_info')->nullable(); // Store additional device information
            $table->timestamp('last_heartbeat')->nullable();
            $table->timestamp('last_attendance_record')->nullable();
            $table->integer('total_heartbeats')->default(0);
            $table->integer('total_attendance_records')->default(0);
            $table->boolean('is_online')->default(false);
            $table->string('status')->default('unknown'); // online, offline, error, maintenance
            $table->text('last_error')->nullable();
            $table->timestamps();
            
            $table->index(['serial_number']);
            $table->index(['is_online']);
            $table->index(['last_heartbeat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteko_devices');
    }
};
