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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->enum('recommendation_type', ['learning_style', 'performance', 'hybrid']);
            $table->decimal('relevance_score', 5, 2);
            $table->text('reason');
            $table->string('algorithm_version', 50);
            $table->boolean('is_viewed')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'content_id']);
            $table->index(['student_id', 'relevance_score']);
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
