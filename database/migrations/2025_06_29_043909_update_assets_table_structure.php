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
        Schema::table('assets', function (Blueprint $table) {
            // Add new columns according to user requirements (if they don't already exist)
            if (!Schema::hasColumn('assets', 'service_tag_number')) {
                $table->string('service_tag_number')->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('assets', 'finance_tag_number')) {
                $table->string('finance_tag_number')->nullable()->after('service_tag_number');
            }
            if (!Schema::hasColumn('assets', 'model_name')) {
                $table->string('model_name')->nullable()->after('asset_category_id');
            }
            if (!Schema::hasColumn('assets', 'model_number')) {
                $table->string('model_number')->nullable()->after('model_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'service_tag_number',
                'finance_tag_number', 
                'model_name',
                'model_number'
            ]);
        });
    }
};
