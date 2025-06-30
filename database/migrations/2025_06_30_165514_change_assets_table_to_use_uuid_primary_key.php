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
        // First, we need to handle any existing data
        // Note: This migration assumes the table is empty or you have a strategy for existing data
        
        Schema::table('assets', function (Blueprint $table) {
            // Drop foreign key constraints that reference the old id
            $table->dropForeign(['assigned_to']);
        });
        
        // Truncate the table since we're changing the primary key type
        // WARNING: This will delete all existing data
        \DB::table('assets')->truncate();
        
        Schema::table('assets', function (Blueprint $table) {
            // Drop the old auto-incrementing id column
            $table->dropColumn('id');
            
            // Add UUID as primary key
            $table->uuid('id')->primary()->first();
            
            // Re-add the foreign key constraint
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['assigned_to']);
            
            // Drop UUID primary key
            $table->dropColumn('id');
            
            // Add back auto-incrementing id
            $table->id()->first();
            
            // Re-add foreign key constraint
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });
    }
};
