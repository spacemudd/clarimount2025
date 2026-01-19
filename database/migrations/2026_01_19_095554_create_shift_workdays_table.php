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
        Schema::create('shift_workdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->tinyInteger('weekday'); // 0=Sunday, 1=Monday, ..., 6=Saturday
            $table->boolean('is_workday')->default(true);
            $table->timestamps();
            
            // Unique constraint: one record per shift per weekday
            $table->unique(['shift_id', 'weekday'], 'shift_workdays_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_workdays');
    }
};
