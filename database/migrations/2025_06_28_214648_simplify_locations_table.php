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
        Schema::table('locations', function (Blueprint $table) {
            // Drop the detailed address columns
            $table->dropColumn([
                'address',
                'city', 
                'state',
                'country',
                'postal_code',
                'phone',
                'contact_person',
                'contact_email'
            ]);
            
            // Add simple location fields
            $table->string('building')->nullable()->after('name');
            $table->string('office_number')->nullable()->after('building');
            
            // Modify code to be unique per company instead of globally unique
            $table->dropUnique(['code']);
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Add back the detailed address columns
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            
            // Remove simple location fields
            $table->dropColumn(['building', 'office_number']);
            
            // Revert code to be globally unique
            $table->dropUnique(['company_id', 'code']);
            $table->unique(['code']);
        });
    }
};
