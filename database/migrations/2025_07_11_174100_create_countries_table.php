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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->unique()->comment('ISO 3166-1 alpha-2 code');
            $table->string('code_alpha3', 3)->unique()->comment('ISO 3166-1 alpha-3 code');
            $table->string('name_en')->comment('English name');
            $table->string('name_ar')->comment('Arabic name');
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
        Schema::dropIfExists('countries');
    }
};
