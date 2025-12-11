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
        Schema::create('competency_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('competency_name');
            $table->decimal('current_level', 5, 2);
            $table->decimal('target_level', 5, 2);
            $table->decimal('progress_percentage', 5, 2);
            $table->date('last_assessment_date')->nullable();
            $table->json('achievements')->nullable(); // Milestones achieved
            $table->timestamps();
            
            $table->index(['student_id', 'subject']);
            $table->index(['competency_name', 'current_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competency_maps');
    }
};
