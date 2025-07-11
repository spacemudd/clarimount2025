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
            // Drop the old nationality index first
            $table->dropIndex(['nationality']);
            
            // Drop the old string columns
            $table->dropColumn(['nationality', 'residence_country']);
            
            // Add new foreign key columns
            $table->foreignId('nationality_id')
                ->nullable()
                ->after('last_name')
                ->constrained('nationalities')
                ->nullOnDelete();
                
            $table->foreignId('residence_country_id')
                ->nullable()
                ->after('nationality_id')
                ->constrained('countries')
                ->nullOnDelete();
            
            // Add indexes for better performance
            $table->index(['nationality_id']);
            $table->index(['residence_country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign key constraints and indexes
            $table->dropForeign(['nationality_id']);
            $table->dropForeign(['residence_country_id']);
            $table->dropIndex(['nationality_id']);
            $table->dropIndex(['residence_country_id']);
            
            // Drop the foreign key columns
            $table->dropColumn(['nationality_id', 'residence_country_id']);
            
            // Add back the old string columns
            $table->string('nationality')->nullable()->after('last_name');
            $table->string('residence_country')->nullable()->after('nationality');
            
            // Add back the old index
            $table->index(['nationality']);
        });
    }
};
