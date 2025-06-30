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
            $table->unsignedBigInteger('asset_template_id')->nullable()->after('asset_category_id');
            $table->string('manufacturer')->nullable()->after('model_number');
            $table->string('image_path')->nullable()->after('notes');
            
            $table->foreign('asset_template_id')->references('id')->on('asset_templates')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_template_id']);
            $table->dropColumn(['asset_template_id', 'manufacturer', 'image_path']);
        });
    }
};
