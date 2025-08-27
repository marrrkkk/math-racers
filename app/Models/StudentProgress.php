<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgress extends Model
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
    'total_points',
    'badges_earned',
    'mastery_level',
    'last_activity',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'question_type' => QuestionType::class,
    'grade_level' => 'integer',
    'total_points' => 'integer',
    'badges_earned' => 'array',
    'mastery_level' => 'decimal:2',
    'last_activity' => 'datetime',
  ];

  /**
   * Get the student this progress belongs to.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(User::class, 'student_id');
  }

  /**
   * Scope to filter progress by grade level.
   */
  public function scopeForGrade($query, int $gradeLevel)
  {
    return $query->where('grade_level', $gradeLevel);
  }

  /**
   * Scope to filter progress by question type.
   */
  public function scopeOfType($query, QuestionType $type)
  {
    return $query->where('question_type', $type);
  }

  /**
   * Scope to filter progress by student.
   */
  public function scopeForStudent($query, int $studentId)
  {
    return $query->where('student_id', $studentId);
  }

  /**
   * Add points to the student's total.
   */
  public function addPoints(int $points): void
  {
    $this->total_points += $points;
    $this->last_activity = now();
    $this->save();
  }

  /**
   * Update mastery level based on recent performance.
   */
  public function updateMasteryLevel(float $newMastery): void
  {
    // Use weighted average to smooth mastery level changes
    $currentWeight = 0.7;
    $newWeight = 0.3;

    $this->mastery_level = (float) ($this->mastery_level * $currentWeight) + ($newMastery * $newWeight);
    $this->last_activity = now();
    $this->save();
  }

  /**
   * Award a badge to the student.
   */
  public function awardBadge(string $badgeType, array $badgeData = []): bool
  {
    $badges = $this->badges_earned ?? [];

    // Check if badge already exists
    foreach ($badges as $badge) {
      if ($badge['type'] === $badgeType) {
        return false; // Badge already earned
      }
    }

    // Add new badge
    $badges[] = [
      'type' => $badgeType,
      'earned_at' => now()->toISOString(),
      'data' => $badgeData,
    ];

    $this->badges_earned = $badges;
    $this->last_activity = now();
    $this->save();

    return true;
  }

  /**
   * Check if student has earned a specific badge.
   */
  public function hasBadge(string $badgeType): bool
  {
    $badges = $this->badges_earned ?? [];

    foreach ($badges as $badge) {
      if ($badge['type'] === $badgeType) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get all badges of a specific type.
   */
  public function getBadgesOfType(string $badgeType): array
  {
    $badges = $this->badges_earned ?? [];

    return array_filter($badges, fn($badge) => $badge['type'] === $badgeType);
  }

  /**
   * Get the total number of badges earned.
   */
  public function getTotalBadgesAttribute(): int
  {
    return count($this->badges_earned ?? []);
  }

  /**
   * Get mastery level as a percentage.
   */
  public function getMasteryPercentageAttribute(): float
  {
    return round((float) $this->mastery_level, 2);
  }

  /**
   * Get mastery level category.
   */
  public function getMasteryCategoryAttribute(): string
  {
    $mastery = (float) $this->mastery_level;

    return match (true) {
      $mastery >= 90 => 'Expert',
      $mastery >= 80 => 'Advanced',
      $mastery >= 70 => 'Proficient',
      $mastery >= 60 => 'Developing',
      $mastery >= 50 => 'Beginning',
      default => 'Needs Support'
    };
  }

  /**
   * Check if student has achieved mastery (80% or higher).
   */
  public function hasMastery(): bool
  {
    return (float) $this->mastery_level >= 80.0;
  }

  /**
   * Get the student's rank based on points for this topic and grade.
   */
  public function getRankAttribute(): int
  {
    return self::where('question_type', $this->question_type)
      ->where('grade_level', $this->grade_level)
      ->where('total_points', '>', $this->total_points)
      ->count() + 1;
  }

  /**
   * Get recent activity status.
   */
  public function getActivityStatusAttribute(): string
  {
    if (!$this->last_activity) {
      return 'Never Active';
    }

    $daysSinceActivity = now()->diffInDays($this->last_activity);

    return match (true) {
      $daysSinceActivity === 0 => 'Active Today',
      $daysSinceActivity === 1 => 'Active Yesterday',
      $daysSinceActivity <= 7 => 'Active This Week',
      $daysSinceActivity <= 30 => 'Active This Month',
      default => 'Inactive'
    };
  }

  /**
   * Get validation rules for student progress creation.
   */
  public static function validationRules(): array
  {
    return [
      'student_id' => 'required|exists:users,id',
      'question_type' => 'required|in:addition,subtraction,multiplication,division',
      'grade_level' => 'required|integer|between:1,3',
      'total_points' => 'integer|min:0',
      'badges_earned' => 'nullable|array',
      'mastery_level' => 'numeric|between:0,100',
    ];
  }
}
