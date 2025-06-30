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
        Schema::table('asset_templates', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('default_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_templates', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
