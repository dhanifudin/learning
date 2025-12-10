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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_number', 50)->unique();
            $table->enum('grade_level', ['10', '11', '12']); // SMA/SMK grades
            $table->string('class', 50); // e.g., '10 IPA 1', '11 IPS 2'
            $table->string('major', 100)->nullable(); // e.g., 'IPA' (Science), 'IPS' (Social)
            $table->json('learning_interests')->nullable();
            $table->year('enrollment_year');
            $table->enum('status', ['active', 'inactive', 'graduated'])->default('active');
            $table->boolean('profile_completed')->default(false);
            $table->enum('preferred_language', ['id', 'en'])->default('id');
            $table->timestamps();

            $table->index(['grade_level', 'status']);
            $table->index('enrollment_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};