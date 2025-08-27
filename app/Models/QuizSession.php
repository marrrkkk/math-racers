<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizSession extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'student_id',
    'question_type',
    'grade_level',
    'total_questions',
    'question_ids',
    'correct_answers',
    'points_earned',
    'accuracy',
    'time_taken',
    'completed_at',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'question_type' => QuestionType::class,
    'grade_level' => 'integer',
    'total_questions' => 'integer',
    'question_ids' => 'array',
    'correct_answers' => 'integer',
    'points_earned' => 'integer',
    'accuracy' => 'decimal:2',
    'time_taken' => 'integer',
    'completed_at' => 'datetime',
  ];

  /**
   * Get the student who took this quiz.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(User::class, 'student_id');
  }

  /**
   * Get all answers for this quiz session.
   */
  public function answers(): HasMany
  {
    return $this->hasMany(QuizAnswer::class);
  }

  /**
   * Get the questions for this quiz session in the correct order.
   */
  public function getQuestionsAttribute()
  {
    if (empty($this->question_ids)) {
      return collect();
    }

    // Get questions in the order specified by question_ids
    $questions = Question::whereIn('id', $this->question_ids)->get()->keyBy('id');

    // Return questions in the correct order
    return collect($this->question_ids)->map(function ($id) use ($questions) {
      return $questions->get($id);
    })->filter();
  }

  /**
   * Scope to filter completed quiz sessions.
   */
  public function scopeCompleted($query)
  {
    return $query->whereNotNull('completed_at');
  }

  /**
   * Scope to filter quiz sessions by grade level.
   */
  public function scopeForGrade($query, int $gradeLevel)
  {
    return $query->where('grade_level', $gradeLevel);
  }

  /**
   * Scope to filter quiz sessions by question type.
   */
  public function scopeOfType($query, QuestionType $type)
  {
    return $query->where('question_type', $type);
  }

  /**
   * Calculate and return the accuracy percentage.
   */
  public function getAccuracyAttribute(): float
  {
    if ($this->total_questions === 0) {
      return 0.0;
    }

    return round(($this->correct_answers / $this->total_questions) * 100, 2);
  }

  /**
   * Calculate score based on correct answers, accuracy, and time bonus.
   */
  public function calculateScore(): int
  {
    $scoringService = new \App\Services\ScoringService();
    return $scoringService->calculateQuizScore($this);
  }

  /**
   * Calculate time bonus based on completion time.
   */
  public function calculateTimeBonus(): int
  {
    if ($this->time_taken <= 0) {
      return 0;
    }

    // Award bonus points for faster completion
    // Maximum 30 seconds per question for full bonus
    $optimalTime = $this->total_questions * 30;

    if ($this->time_taken <= $optimalTime) {
      return min(25, (int) (($optimalTime - $this->time_taken) / 10));
    }

    return 0;
  }

  /**
   * Check if the quiz session is completed.
   */
  public function isCompleted(): bool
  {
    return !is_null($this->completed_at);
  }

  /**
   * Mark the quiz session as completed and calculate final score.
   */
  public function complete(): void
  {
    $this->completed_at = now();
    $this->points_earned = $this->calculateScore();
    $this->save();

    // Update student progress after completion
    $progressService = new \App\Services\ProgressService();
    $progressService->updateProgressFromQuizSession($this);
  }

  /**
   * Get the average time per question.
   */
  public function getAverageTimePerQuestionAttribute(): float
  {
    if ($this->total_questions === 0) {
      return 0.0;
    }

    return round($this->time_taken / $this->total_questions, 2);
  }

  /**
   * Get performance rating based on accuracy.
   */
  public function getPerformanceRatingAttribute(): string
  {
    $accuracy = $this->accuracy;

    return match (true) {
      $accuracy >= 90 => 'Excellent',
      $accuracy >= 80 => 'Very Good',
      $accuracy >= 70 => 'Good',
      $accuracy >= 60 => 'Fair',
      default => 'Needs Improvement'
    };
  }

  /**
   * Get validation rules for quiz session creation.
   */
  public static function validationRules(): array
  {
    return [
      'student_id' => 'required|exists:users,id',
      'question_type' => 'required|in:addition,subtraction,multiplication,division',
      'grade_level' => 'required|integer|between:1,3',
      'total_questions' => 'required|integer|min:1|max:20',
    ];
  }
}
