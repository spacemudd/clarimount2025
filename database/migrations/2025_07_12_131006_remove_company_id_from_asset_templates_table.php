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
            // Drop the foreign key constraint first
            $table->dropForeign(['company_id']);
            
            // Drop the index
            $table->dropIndex(['company_id', 'asset_category_id']);
            
            // Drop the column
            $table->dropColumn('company_id');
            
            // Since we're making all templates global, we can remove the is_global column too
            // and the related index
            $table->dropIndex(['is_global', 'usage_count']);
            $table->dropColumn('is_global');
            
            // Create a new index for performance without company_id
            $table->index(['asset_category_id', 'usage_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_templates', function (Blueprint $table) {
            // Re-add the columns
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('is_global')->default(true);
            
            // Re-add the indexes
            $table->index(['company_id', 'asset_category_id']);
            $table->index(['is_global', 'usage_count']);
            
            // Drop the new index
            $table->dropIndex(['asset_category_id', 'usage_count']);
        });
    }
};
