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
        Schema::table('attendance_imports', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['team_id']);
            
            // Make team_id nullable
            $table->unsignedBigInteger('team_id')->nullable()->change();
            
            // Add the foreign key constraint back with SET NULL on delete
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_imports', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['team_id']);
            
            // Make team_id not nullable again
            $table->unsignedBigInteger('team_id')->nullable(false)->change();
            
            // Add the foreign key constraint back with CASCADE on delete
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
        });
    }
};
