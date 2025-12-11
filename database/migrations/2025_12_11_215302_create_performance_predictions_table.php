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
        Schema::create('performance_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('prediction_type'); // 'exam_score', 'risk_assessment', 'completion_time'
            $table->decimal('predicted_value', 10, 4);
            $table->decimal('confidence_score', 5, 4); // 0.0 to 1.0
            $table->json('contributing_factors'); // What factors led to this prediction
            $table->timestamp('prediction_date');
            $table->date('target_date'); // When this prediction is for
            $table->decimal('actual_outcome', 10, 4)->nullable(); // Actual result when available
            $table->decimal('accuracy_score', 5, 4)->nullable(); // How accurate the prediction was
            $table->timestamps();
            
            $table->index(['student_id', 'prediction_type', 'target_date'], 'perf_pred_student_type_date_idx');
            $table->index(['prediction_date', 'accuracy_score'], 'perf_pred_date_accuracy_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_predictions');
    }
};
