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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->enum('question_type', ['addition', 'subtraction', 'multiplication', 'division']);
            $table->tinyInteger('grade_level')->comment('Grade level (1-3)');
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('correct_answer');
            $table->json('options')->nullable()->comment('For multiple choice questions');
            $table->string('deped_competency')->comment('DepEd K-12 competency alignment');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['grade_level', 'question_type']);
            $table->index('deped_competency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
