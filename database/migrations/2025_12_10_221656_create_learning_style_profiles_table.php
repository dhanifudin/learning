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
        Schema::create('learning_style_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->decimal('visual_score', 5, 2);
            $table->decimal('auditory_score', 5, 2);
            $table->decimal('kinesthetic_score', 5, 2);
            $table->enum('dominant_style', ['visual', 'auditory', 'kinesthetic', 'mixed']);
            $table->json('survey_data');
            $table->timestamp('analysis_date');
            $table->decimal('ai_confidence_score', 5, 2);
            $table->timestamps();

            $table->index(['student_id', 'analysis_date']);
            $table->index('dominant_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_style_profiles');
    }
};