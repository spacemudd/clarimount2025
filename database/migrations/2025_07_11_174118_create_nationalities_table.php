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
        Schema::create('nationalities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Short code for nationality');
            $table->string('name_en')->comment('English nationality name');
            $table->string('name_ar')->comment('Arabic nationality name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['is_active']);
            $table->index(['name_en']);
            $table->index(['name_ar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nationalities');
    }
};
