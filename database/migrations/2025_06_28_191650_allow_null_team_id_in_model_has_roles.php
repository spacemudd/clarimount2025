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
        // Allow null team_id for global roles in model_has_roles table
        if (Schema::hasTable('model_has_roles') && Schema::hasColumn('model_has_roles', 'team_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                // Drop the existing primary key constraint
                $table->dropPrimary('model_has_roles_role_model_type_primary');
                
                // Make team_id nullable
                $table->unsignedBigInteger('team_id')->nullable()->change();
                
                // Create new unique constraint that handles nullable team_id
                // For records with team_id, ensure uniqueness within that team
                // For records with team_id = NULL (global), ensure uniqueness globally
                $table->unique(['role_id', 'model_id', 'model_type', 'team_id'], 'model_has_roles_unique_constraint');
            });
        }
        
        // Also allow null team_id for global permissions in model_has_permissions table  
        if (Schema::hasTable('model_has_permissions') && Schema::hasColumn('model_has_permissions', 'team_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                // Drop the existing primary key constraint
                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                
                // Make team_id nullable
                $table->unsignedBigInteger('team_id')->nullable()->change();
                
                // Create new unique constraint that handles nullable team_id
                $table->unique(['permission_id', 'model_id', 'model_type', 'team_id'], 'model_has_permissions_unique_constraint');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('model_has_roles') && Schema::hasColumn('model_has_roles', 'team_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique('model_has_roles_unique_constraint');
                
                // Make team_id not nullable
                $table->unsignedBigInteger('team_id')->nullable(false)->change();
                
                // Recreate the original primary key
                $table->primary(['team_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
            });
        }
        
        if (Schema::hasTable('model_has_permissions') && Schema::hasColumn('model_has_permissions', 'team_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                // Drop the unique constraint
                $table->dropUnique('model_has_permissions_unique_constraint');
                
                // Make team_id not nullable
                $table->unsignedBigInteger('team_id')->nullable(false)->change();
                
                // Recreate the original primary key
                $table->primary(['team_id', 'permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
        }
    }
}; 