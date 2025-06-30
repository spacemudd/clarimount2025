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
        Schema::table('asset_assignments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['asset_id']);
            
            // Drop the existing asset_id column
            $table->dropColumn('asset_id');
            
            // Add UUID asset_id column
            $table->uuid('asset_id')->after('id');
            
            // Add the foreign key constraint
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            // Drop the UUID foreign key constraint
            $table->dropForeign(['asset_id']);
            
            // Drop the UUID asset_id column
            $table->dropColumn('asset_id');
            
            // Add back the integer asset_id column
            $table->foreignId('asset_id')->after('id')->constrained()->onDelete('cascade');
        });
    }
};
