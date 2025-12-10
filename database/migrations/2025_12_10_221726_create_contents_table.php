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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('subject', 100); // e.g., 'Mathematics'
            $table->string('topic', 255); // e.g., 'Trigonometry', 'Calculus'
            $table->enum('grade_level', ['10', '11', '12']);
            $table->enum('content_type', ['video', 'pdf', 'audio', 'interactive', 'text']);
            $table->enum('target_learning_style', ['visual', 'auditory', 'kinesthetic', 'all']);
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced']);
            $table->string('file_url', 500)->nullable();
            $table->string('external_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('views_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['subject', 'grade_level']);
            $table->index(['content_type', 'target_learning_style']);
            $table->index(['difficulty_level', 'is_active']);
            
            // Only add fulltext index if not using SQLite
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'description', 'topic']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};