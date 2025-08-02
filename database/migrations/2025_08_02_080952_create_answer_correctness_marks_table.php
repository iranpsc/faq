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
        Schema::create('answer_correctness_marks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('answer_id');
            $table->unsignedBigInteger('marker_user_id');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->foreign('answer_id')->references('id')->on('answers')->onDelete('cascade');
            $table->foreign('marker_user_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure one user can mark one answer only once
            $table->unique(['marker_user_id', 'answer_id']);

            // Index for performance
            $table->index('answer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_correctness_marks');
    }
};
