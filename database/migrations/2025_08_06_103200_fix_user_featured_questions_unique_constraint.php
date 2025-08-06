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
        Schema::table('user_featured_questions', function (Blueprint $table) {
            // Drop the existing unique constraint that only includes user_id and question_id
            $table->dropUnique(['user_id', 'question_id']);

            // Add new unique constraint that includes type to allow multiple types per user-question
            $table->unique(['user_id', 'question_id', 'type'], 'user_featured_questions_user_id_question_id_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_featured_questions', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('user_featured_questions_user_id_question_id_type_unique');

            // Restore the original unique constraint (this might fail if data already exists)
            $table->unique(['user_id', 'question_id']);
        });
    }
};
