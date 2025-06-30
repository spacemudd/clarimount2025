<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if the asset_templates table exists
        if (!Schema::hasTable('asset_templates')) {
            return; // Skip if table doesn't exist yet
        }
        
        // Check if there are any asset templates without categories
        $templatesWithoutCategory = DB::table('asset_templates')
            ->whereNull('asset_category_id')
            ->get();

        // If there are templates without categories, we need to assign them a default category
        if ($templatesWithoutCategory->count() > 0) {
            foreach ($templatesWithoutCategory as $template) {
                // Find the first active category for this company, or create a default one
                $defaultCategory = DB::table('asset_categories')
                    ->where('company_id', $template->company_id)
                    ->where('is_active', true)
                    ->first();

                if (!$defaultCategory) {
                    // Create a default "Uncategorized" category for this company
                    $defaultCategoryId = DB::table('asset_categories')->insertGetId([
                        'name' => 'Uncategorized',
                        'code' => 'UNCAT',
                        'description' => 'Default category for uncategorized assets',
                        'company_id' => $template->company_id,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        '_lft' => 1,
                        '_rgt' => 2,
                        'parent_id' => null,
                    ]);
                } else {
                    $defaultCategoryId = $defaultCategory->id;
                }

                // Update the template with the default category
                DB::table('asset_templates')
                    ->where('id', $template->id)
                    ->update(['asset_category_id' => $defaultCategoryId]);
            }
        }

        // Now make asset_category_id required in asset_templates table
        if (Schema::hasTable('asset_templates') && Schema::hasColumn('asset_templates', 'asset_category_id')) {
            Schema::table('asset_templates', function (Blueprint $table) {
                $table->unsignedBigInteger('asset_category_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert asset_category_id back to nullable in asset_templates
        if (Schema::hasTable('asset_templates') && Schema::hasColumn('asset_templates', 'asset_category_id')) {
            Schema::table('asset_templates', function (Blueprint $table) {
                $table->unsignedBigInteger('asset_category_id')->nullable()->change();
            });
        }
    }
}; 