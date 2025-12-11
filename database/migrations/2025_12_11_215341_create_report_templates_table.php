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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('report_type', ['student', 'class', 'teacher', 'school']);
            $table->json('template_structure'); // Template configuration
            $table->json('visualization_config'); // Chart and graph settings
            $table->json('automated_schedule')->nullable(); // Cron-like schedule
            $table->json('recipient_rules')->nullable(); // Who receives automated reports
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['report_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
