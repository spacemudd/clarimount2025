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
        // This migration is intentionally empty as Spatie Permission package
        // handles team support through its configuration.
        // The team_foreign_key will be added to roles and model_has_* tables
        // automatically when teams are enabled in config/permission.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is intentionally empty as Spatie Permission package
        // handles team support through its configuration.
    }
};
