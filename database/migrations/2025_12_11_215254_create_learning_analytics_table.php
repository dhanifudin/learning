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
        Schema::create('learning_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('metric_type'); // 'engagement', 'performance', 'time_spent', etc.
            $table->decimal('metric_value', 10, 4);
            $table->date('calculation_date');
            $table->json('context_data')->nullable(); // Additional metadata
            $table->enum('aggregation_period', ['daily', 'weekly', 'monthly', 'quarterly']);
            $table->timestamps();
            
            $table->index(['student_id', 'metric_type', 'calculation_date']);
            $table->index(['metric_type', 'aggregation_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_analytics');
    }
};
