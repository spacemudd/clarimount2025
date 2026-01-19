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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('shift_id')
                ->nullable()
                ->after('fingerprint_device_id')
                ->constrained('shifts')
                ->onDelete('set null');
            
            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropIndex(['shift_id']);
            $table->dropColumn('shift_id');
        });
    }
};
