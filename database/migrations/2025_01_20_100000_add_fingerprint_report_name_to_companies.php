<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('fingerprint_report_name')->nullable()->after('slug');
            $table->index('fingerprint_report_name', 'idx_fingerprint_report_name');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex('idx_fingerprint_report_name');
            $table->dropColumn('fingerprint_report_name');
        });
    }
};
