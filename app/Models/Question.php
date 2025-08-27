<?php

namespace App\Models;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'question_text',
    'question_type',
    'grade_level',
    'difficulty',
    'correct_answer',
    'options',
    'deped_competency',
    'created_by',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'question_type' => QuestionType::class,
    'difficulty' => Difficulty::class,
    'grade_level' => 'integer',
    'options' => 'array',
  ];

  /**
   * Get the user who created this question.
   */
  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Get all quiz answers for this question.
   */
  public function quizAnswers(): HasMany
  {
    return $this->hasMany(QuizAnswer::class);
  }

  /**
   * Scope to filter questions by grade level.
   */
  public function scopeForGrade($query, int $gradeLevel)
  {
    return $query->where('grade_level', $gradeLevel);
  }

  /**
   * Scope to filter questions by type.
   */
  public function scopeOfType($query, QuestionType $type)
  {
    return $query->where('question_type', $type);
  }

  /**
   * Scope to filter questions by difficulty.
   */
  public function scopeWithDifficulty($query, Difficulty $difficulty)
  {
    return $query->where('difficulty', $difficulty);
  }

  /**
   * Check if the given answer is correct.
   */
  public function isCorrectAnswer(string $answer): bool
  {
    return trim(strtolower($answer)) === trim(strtolower($this->correct_answer));
  }

  /**
   * Get the points value for this question based on difficulty.
   */
  public function getPointsAttribute(): int
  {
    return $this->difficulty->points();
  }

  /**
   * Get formatted question text with proper spacing.
   */
  public function getFormattedQuestionAttribute(): string
  {
    return trim($this->question_text);
  }

  /**
   * Check if this is a multiple choice question.
   */
  public function isMultipleChoice(): bool
  {
    return !empty($this->options) && is_array($this->options);
  }

  /**
   * Get validation rules for question creation.
   */
  public static function validationRules(): array
  {
    return [
      'question_text' => 'required|string|max:1000',
      'question_type' => 'required|in:addition,subtraction,multiplication,division',
      'grade_level' => 'required|integer|between:1,3',
      'difficulty' => 'required|in:easy,medium,hard',
      'correct_answer' => 'required|string|max:255',
      'options' => 'nullable|array|max:4',
      'options.*' => 'string|max:255',
      'deped_competency' => 'required|string|max:255',
    ];
  }
}
