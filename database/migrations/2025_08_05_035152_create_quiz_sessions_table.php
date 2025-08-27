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
        Schema::create('quiz_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('question_type', ['addition', 'subtraction', 'multiplication', 'division']);
            $table->tinyInteger('grade_level')->comment('Grade level (1-3)');
            $table->integer('total_questions');
            $table->integer('correct_answers')->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('time_taken')->comment('Time taken in seconds');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['student_id', 'completed_at']);
            $table->index(['grade_level', 'question_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_sessions');
    }
};
