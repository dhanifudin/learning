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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('learning_style_surveys');
            $table->foreignId('student_id')->constrained('students');
            $table->json('responses'); // Question ID => Answer value mapping
            $table->json('calculated_scores')->nullable(); // Visual, auditory, kinesthetic scores
            $table->enum('status', ['started', 'in_progress', 'completed', 'abandoned'])->default('started');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->string('session_id')->nullable();
            $table->json('metadata')->nullable(); // Device info, IP, etc.
            $table->timestamps();
            
            $table->unique(['survey_id', 'student_id']);
            $table->index(['student_id', 'status']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
