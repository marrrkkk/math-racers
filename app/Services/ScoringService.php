<?php

namespace App\Services;

use App\Models\QuizSession;
use App\Models\QuizAnswer;
use App\Models\Question;

class ScoringService
{
  /**
   * Calculate total score for a quiz session.
   */
  public function calculateQuizScore(QuizSession $quizSession): int
  {
    $basePoints = $this->calculateBasePoints($quizSession);
    $accuracyBonus = $this->calculateAccuracyBonus($quizSession);
    $timeBonus = $this->calculateTimeBonus($quizSession);
    $difficultyBonus = $this->calculateDifficultyBonus($quizSession);

    return (int) ($basePoints + $accuracyBonus + $timeBonus + $difficultyBonus);
  }

  /**
   * Calculate base points from correct answers.
   */
  protected function calculateBasePoints(QuizSession $quizSession): int
  {
    return $quizSession->correct_answers * 10;
  }

  /**
   * Calculate accuracy bonus (up to 50 points).
   */
  protected function calculateAccuracyBonus(QuizSession $quizSession): float
  {
    if ($quizSession->total_questions === 0) {
      return 0;
    }

    $accuracy = $quizSession->correct_answers / $quizSession->total_questions;
    return $accuracy * 50;
  }

  /**
   * Calculate time bonus based on completion speed.
   */
  protected function calculateTimeBonus(QuizSession $quizSession): int
  {
    if ($quizSession->time_taken <= 0) {
      return 0;
    }

    // Optimal time: 30 seconds per question
    $optimalTime = $quizSession->total_questions * 30;

    // Award bonus for completing under optimal time
    if ($quizSession->time_taken <= $optimalTime) {
      $timeSaved = $optimalTime - $quizSession->time_taken;
      return min(25, (int) ($timeSaved / 10)); // Max 25 bonus points
    }

    return 0;
  }

  /**
   * Calculate difficulty bonus based on question difficulty.
   */
  protected function calculateDifficultyBonus(QuizSession $quizSession): int
  {
    $answers = $quizSession->answers()->with('question')->get();
    $difficultyBonus = 0;

    foreach ($answers as $answer) {
      if ($answer->is_correct && $answer->question) {
        $difficultyBonus += $answer->question->difficulty->points();
      }
    }

    return $difficultyBonus;
  }

  /**
   * Calculate individual answer score.
   */
  public function calculateAnswerScore(QuizAnswer $answer): int
  {
    if (!$answer->is_correct) {
      return 0;
    }

    $basePoints = $answer->question->difficulty->points();
    $timeBonus = $this->calculateAnswerTimeBonus($answer);

    return $basePoints + $timeBonus;
  }

  /**
   * Calculate time bonus for individual answer.
   */
  protected function calculateAnswerTimeBonus(QuizAnswer $answer): int
  {
    if (!$answer->is_correct || $answer->time_taken <= 0) {
      return 0;
    }

    // Quick answer bonus (under 10 seconds)
    if ($answer->time_taken <= 10) {
      return 5;
    }

    // Moderate speed bonus (under 20 seconds)
    if ($answer->time_taken <= 20) {
      return 2;
    }

    return 0;
  }

  /**
   * Calculate streak bonus for consecutive correct answers.
   */
  public function calculateStreakBonus(array $answers): int
  {
    $maxStreak = 0;
    $currentStreak = 0;

    foreach ($answers as $answer) {
      if ($answer['is_correct']) {
        $currentStreak++;
        $maxStreak = max($maxStreak, $currentStreak);
      } else {
        $currentStreak = 0;
      }
    }

    // Award bonus for streaks of 3 or more
    if ($maxStreak >= 3) {
      return min(20, ($maxStreak - 2) * 5); // 5 points per streak above 2
    }

    return 0;
  }

  /**
   * Calculate performance multiplier based on grade level.
   */
  public function getGradeMultiplier(int $gradeLevel): float
  {
    return match ($gradeLevel) {
      1 => 1.0,  // Grade 1: Base multiplier
      2 => 1.1,  // Grade 2: 10% bonus
      3 => 1.2,  // Grade 3: 20% bonus
      default => 1.0,
    };
  }

  /**
   * Get performance rating based on score and accuracy.
   */
  public function getPerformanceRating(QuizSession $quizSession): array
  {
    $accuracy = $quizSession->accuracy;
    $pointsPerQuestion = $quizSession->total_questions > 0
      ? $quizSession->points_earned / $quizSession->total_questions
      : 0;

    $rating = match (true) {
      $accuracy >= 90 && $pointsPerQuestion >= 15 => 'Excellent',
      $accuracy >= 80 && $pointsPerQuestion >= 12 => 'Very Good',
      $accuracy >= 70 && $pointsPerQuestion >= 10 => 'Good',
      $accuracy >= 60 && $pointsPerQuestion >= 8 => 'Fair',
      default => 'Needs Improvement'
    };

    $stars = match ($rating) {
      'Excellent' => 5,
      'Very Good' => 4,
      'Good' => 3,
      'Fair' => 2,
      default => 1,
    };

    return [
      'rating' => $rating,
      'stars' => $stars,
      'accuracy' => $accuracy,
      'points_per_question' => round($pointsPerQuestion, 2),
    ];
  }

  /**
   * Calculate points needed for next achievement level.
   */
  public function getPointsToNextLevel(int $currentPoints): array
  {
    $levels = [
      ['name' => 'Beginner', 'points' => 0],
      ['name' => 'Learner', 'points' => 100],
      ['name' => 'Explorer', 'points' => 250],
      ['name' => 'Achiever', 'points' => 500],
      ['name' => 'Expert', 'points' => 1000],
      ['name' => 'Master', 'points' => 2000],
      ['name' => 'Champion', 'points' => 5000],
    ];

    $currentLevel = null;
    $nextLevel = null;

    foreach ($levels as $index => $level) {
      if ($currentPoints >= $level['points']) {
        $currentLevel = $level;
      } else {
        $nextLevel = $level;
        break;
      }
    }

    if (!$nextLevel) {
      // Already at max level
      return [
        'current_level' => $currentLevel,
        'next_level' => null,
        'points_needed' => 0,
        'progress_percentage' => 100,
      ];
    }

    $pointsNeeded = $nextLevel['points'] - $currentPoints;
    $levelRange = $nextLevel['points'] - $currentLevel['points'];
    $progressInLevel = $currentPoints - $currentLevel['points'];
    $progressPercentage = $levelRange > 0 ? ($progressInLevel / $levelRange) * 100 : 0;

    return [
      'current_level' => $currentLevel,
      'next_level' => $nextLevel,
      'points_needed' => $pointsNeeded,
      'progress_percentage' => round($progressPercentage, 2),
    ];
  }
}
