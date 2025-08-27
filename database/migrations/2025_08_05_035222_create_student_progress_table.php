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
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->enum('question_type', ['addition', 'subtraction', 'multiplication', 'division']);
            $table->tinyInteger('grade_level')->comment('Grade level (1-3)');
            $table->integer('total_points')->default(0);
            $table->json('badges_earned')->nullable()->comment('Array of earned badges');
            $table->decimal('mastery_level', 5, 2)->default(0.00)->comment('Mastery percentage (0-100)');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate progress records
            $table->unique(['student_id', 'question_type', 'grade_level']);

            // Indexes for better query performance
            $table->index(['student_id', 'last_activity']);
            $table->index(['grade_level', 'question_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
