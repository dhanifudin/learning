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
        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->enum('grade_level', ['10', '11', '12']);
            $table->string('subject_code', 20); // e.g., 'MTK' (Matematika)
            $table->string('subject_name_id', 255); // Indonesian name
            $table->string('subject_name_en', 255); // English name
            $table->enum('category', ['wajib', 'peminatan', 'lintas_minat']); // Required, Specialization, Cross-interest
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['grade_level', 'subject_code']);
            $table->index(['grade_level', 'category']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
    }
};