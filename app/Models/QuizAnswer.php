<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'quiz_session_id',
    'question_id',
    'student_answer',
    'is_correct',
    'time_taken',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'is_correct' => 'boolean',
    'time_taken' => 'integer',
  ];

  /**
   * Get the quiz session this answer belongs to.
   */
  public function quizSession(): BelongsTo
  {
    return $this->belongsTo(QuizSession::class);
  }

  /**
   * Get the question this answer is for.
   */
  public function question(): BelongsTo
  {
    return $this->belongsTo(Question::class);
  }

  /**
   * Scope to filter correct answers.
   */
  public function scopeCorrect($query)
  {
    return $query->where('is_correct', true);
  }

  /**
   * Scope to filter incorrect answers.
   */
  public function scopeIncorrect($query)
  {
    return $query->where('is_correct', false);
  }

  /**
   * Scope to filter answers by quiz session.
   */
  public function scopeForSession($query, int $sessionId)
  {
    return $query->where('quiz_session_id', $sessionId);
  }

  /**
   * Scope to filter answers by question.
   */
  public function scopeForQuestion($query, int $questionId)
  {
    return $query->where('question_id', $questionId);
  }

  /**
   * Get the points earned for this answer.
   */
  public function getPointsEarnedAttribute(): int
  {
    if (!$this->is_correct) {
      return 0;
    }

    // Base points plus time bonus
    $basePoints = $this->question->points ?? 10;
    $timeBonus = $this->calculateTimeBonus();

    return $basePoints + $timeBonus;
  }

  /**
   * Calculate time bonus for quick answers.
   */
  public function calculateTimeBonus(): int
  {
    if ($this->time_taken <= 0 || !$this->is_correct) {
      return 0;
    }

    // Award bonus for answers under 10 seconds
    if ($this->time_taken <= 10) {
      return 5;
    }

    // Award smaller bonus for answers under 20 seconds
    if ($this->time_taken <= 20) {
      return 2;
    }

    return 0;
  }

  /**
   * Get the response time category.
   */
  public function getResponseTimeCategoryAttribute(): string
  {
    return match (true) {
      $this->time_taken <= 5 => 'Very Fast',
      $this->time_taken <= 10 => 'Fast',
      $this->time_taken <= 20 => 'Normal',
      $this->time_taken <= 30 => 'Slow',
      default => 'Very Slow'
    };
  }

  /**
   * Check if this answer was given quickly.
   */
  public function isQuickAnswer(): bool
  {
    return $this->time_taken <= 10;
  }

  /**
   * Get formatted student answer.
   */
  public function getFormattedAnswerAttribute(): string
  {
    return trim($this->student_answer);
  }

  /**
   * Get validation rules for quiz answer creation.
   */
  public static function validationRules(): array
  {
    return [
      'quiz_session_id' => 'required|exists:quiz_sessions,id',
      'question_id' => 'required|exists:questions,id',
      'student_answer' => 'required|string|max:255',
      'is_correct' => 'required|boolean',
      'time_taken' => 'required|integer|min:0',
    ];
  }
}
