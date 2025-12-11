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
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('report_type', ['student', 'class', 'teacher', 'school']);
            $table->unsignedBigInteger('entity_id'); // Student ID, Class ID, etc.
            $table->json('report_data'); // Generated report content
            $table->date('period_start');
            $table->date('period_end');
            $table->string('generated_by'); // 'system' or user_id
            $table->string('file_path')->nullable(); // PDF/Excel file path
            $table->json('sharing_permissions')->nullable(); // Who can access this report
            $table->timestamp('created_at');
            
            $table->index(['report_type', 'entity_id', 'period_start']);
            $table->index(['created_at', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
    }
};
