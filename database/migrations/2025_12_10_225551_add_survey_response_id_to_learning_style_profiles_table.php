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
        Schema::table('learning_style_profiles', function (Blueprint $table) {
            $table->foreignId('survey_response_id')->nullable()->constrained('survey_responses')->after('student_id');
            $table->index('survey_response_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_style_profiles', function (Blueprint $table) {
            $table->dropForeign(['survey_response_id']);
            $table->dropIndex(['survey_response_id']);
            $table->dropColumn('survey_response_id');
        });
    }
};
