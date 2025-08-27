<?php

namespace App\Services;

use App\Models\QuizSession;
use App\Models\StudentProgress;
use App\Models\User;

class BadgeService
{
  /**
   * Check and award completion badges.
   */
  public function checkCompletionBadges(StudentProgress $progress, QuizSession $quizSession): void
  {
    $student = $progress->student;
    $questionType = $progress->question_type;
    $gradeLevel = $progress->grade_level;

    // Count total completed quizzes for this topic
    $totalQuizzes = QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->count();

    // First Quiz Badge
    if ($totalQuizzes === 1) {
      $progress->awardBadge('first_quiz', [
        'topic' => $questionType->label(),
        'grade' => $gradeLevel,
      ]);
    }

    // Quiz Milestone Badges
    $milestones = [5, 10, 25, 50, 100];
    foreach ($milestones as $milestone) {
      if ($totalQuizzes === $milestone) {
        $progress->awardBadge("quiz_milestone_{$milestone}", [
          'topic' => $questionType->label(),
          'grade' => $gradeLevel,
          'total_quizzes' => $totalQuizzes,
        ]);
      }
    }

    // Daily Streak Badges
    $this->checkDailyStreakBadges($progress, $student, $questionType, $gradeLevel);
  }

  /**
   * Check and award accuracy badges.
   */
  public function checkAccuracyBadges(StudentProgress $progress, QuizSession $quizSession): void
  {
    $accuracy = $quizSession->accuracy;

    // Perfect Score Badge (100% accuracy)
    if ($accuracy === 100.0) {
      $progress->awardBadge('perfect_score', [
        'quiz_id' => $quizSession->id,
        'topic' => $progress->question_type->label(),
        'points_earned' => $quizSession->points_earned,
      ]);
    }

    // High Accuracy Badges
    $accuracyThresholds = [
      95 => 'accuracy_expert',
      90 => 'accuracy_master',
      85 => 'accuracy_achiever',
    ];

    foreach ($accuracyThresholds as $threshold => $badgeType) {
      if ($accuracy >= $threshold && !$progress->hasBadge($badgeType)) {
        $progress->awardBadge($badgeType, [
          'accuracy' => $accuracy,
          'topic' => $progress->question_type->label(),
        ]);
      }
    }

    // Consistent Accuracy Badge (5 quizzes in a row with 80%+ accuracy)
    $this->checkConsistentAccuracyBadge($progress, $quizSession);
  }

  /**
   * Check and award speed badges.
   */
  public function checkSpeedBadges(StudentProgress $progress, QuizSession $quizSession): void
  {
    $averageTimePerQuestion = $quizSession->average_time_per_question;

    // Speed Demon Badge (under 10 seconds per question)
    if ($averageTimePerQuestion <= 10) {
      $progress->awardBadge('speed_demon', [
        'average_time' => $averageTimePerQuestion,
        'topic' => $progress->question_type->label(),
        'quiz_id' => $quizSession->id,
      ]);
    }

    // Quick Thinker Badge (under 15 seconds per question)
    if ($averageTimePerQuestion <= 15 && !$progress->hasBadge('quick_thinker')) {
      $progress->awardBadge('quick_thinker', [
        'average_time' => $averageTimePerQuestion,
        'topic' => $progress->question_type->label(),
      ]);
    }

    // Lightning Fast Badge (under 5 seconds per question with 90%+ accuracy)
    if ($averageTimePerQuestion <= 5 && $quizSession->accuracy >= 90) {
      $progress->awardBadge('lightning_fast', [
        'average_time' => $averageTimePerQuestion,
        'accuracy' => $quizSession->accuracy,
        'topic' => $progress->question_type->label(),
      ]);
    }
  }

  /**
   * Check and award streak badges.
   */
  public function checkStreakBadges(StudentProgress $progress, QuizSession $quizSession): void
  {
    // Get answers for this session to check for streaks
    $answers = $quizSession->answers()->orderBy('created_at')->get();

    $maxStreak = 0;
    $currentStreak = 0;

    foreach ($answers as $answer) {
      if ($answer->is_correct) {
        $currentStreak++;
        $maxStreak = max($maxStreak, $currentStreak);
      } else {
        $currentStreak = 0;
      }
    }

    // Streak Badges
    $streakThresholds = [
      10 => 'streak_master',
      7 => 'streak_champion',
      5 => 'streak_achiever',
      3 => 'streak_starter',
    ];

    foreach ($streakThresholds as $threshold => $badgeType) {
      if ($maxStreak >= $threshold) {
        $progress->awardBadge($badgeType, [
          'streak_length' => $maxStreak,
          'topic' => $progress->question_type->label(),
          'quiz_id' => $quizSession->id,
        ]);
        break; // Award only the highest streak badge
      }
    }
  }

  /**
   * Check and award mastery badges.
   */
  public function checkMasteryBadges(StudentProgress $progress): void
  {
    $masteryLevel = $progress->mastery_level;

    // Mastery Level Badges
    $masteryThresholds = [
      95 => 'mastery_legend',
      90 => 'mastery_expert',
      85 => 'mastery_master',
      80 => 'mastery_achiever',
    ];

    foreach ($masteryThresholds as $threshold => $badgeType) {
      if ($masteryLevel >= $threshold && !$progress->hasBadge($badgeType)) {
        $progress->awardBadge($badgeType, [
          'mastery_level' => $masteryLevel,
          'topic' => $progress->question_type->label(),
          'grade' => $progress->grade_level,
        ]);
        break; // Award only the highest mastery badge
      }
    }
  }

  /**
   * Check and award point milestone badges.
   */
  public function checkPointMilestoneBadges(StudentProgress $progress): void
  {
    $totalPoints = $progress->total_points;

    // Point Milestone Badges
    $pointMilestones = [
      5000 => 'point_legend',
      2000 => 'point_master',
      1000 => 'point_champion',
      500 => 'point_achiever',
      250 => 'point_collector',
      100 => 'point_starter',
    ];

    foreach ($pointMilestones as $milestone => $badgeType) {
      if ($totalPoints >= $milestone && !$progress->hasBadge($badgeType)) {
        $progress->awardBadge($badgeType, [
          'points' => $totalPoints,
          'topic' => $progress->question_type->label(),
        ]);
        break; // Award only the highest point badge
      }
    }
  }

  /**
   * Check daily streak badges.
   */
  protected function checkDailyStreakBadges(StudentProgress $progress, User $student, $questionType, int $gradeLevel): void
  {
    // Get quiz sessions from the last 30 days
    $recentSessions = QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->where('completed_at', '>=', now()->subDays(30))
      ->orderBy('completed_at', 'desc')
      ->get();

    // Group by date
    $sessionsByDate = $recentSessions->groupBy(function ($session) {
      return $session->completed_at->format('Y-m-d');
    });

    // Calculate current streak
    $currentStreak = 0;
    $currentDate = now();

    for ($i = 0; $i < 30; $i++) {
      $dateString = $currentDate->format('Y-m-d');

      if ($sessionsByDate->has($dateString)) {
        $currentStreak++;
      } else {
        break;
      }

      $currentDate->subDay();
    }

    // Award daily streak badges
    $dailyStreakThresholds = [
      30 => 'daily_streak_legend',
      14 => 'daily_streak_champion',
      7 => 'daily_streak_achiever',
      3 => 'daily_streak_starter',
    ];

    foreach ($dailyStreakThresholds as $threshold => $badgeType) {
      if ($currentStreak >= $threshold && !$progress->hasBadge($badgeType)) {
        $progress->awardBadge($badgeType, [
          'streak_days' => $currentStreak,
          'topic' => $progress->question_type->label(),
        ]);
        break;
      }
    }
  }

  /**
   * Check consistent accuracy badge.
   */
  protected function checkConsistentAccuracyBadge(StudentProgress $progress, QuizSession $quizSession): void
  {
    if ($progress->hasBadge('consistent_accuracy')) {
      return;
    }

    // Get last 5 quiz sessions
    $recentSessions = QuizSession::where('student_id', $progress->student_id)
      ->ofType($progress->question_type)
      ->forGrade($progress->grade_level)
      ->completed()
      ->orderBy('completed_at', 'desc')
      ->limit(5)
      ->get();

    if ($recentSessions->count() === 5) {
      $allAbove80 = $recentSessions->every(function ($session) {
        return $session->accuracy >= 80;
      });

      if ($allAbove80) {
        $progress->awardBadge('consistent_accuracy', [
          'sessions_count' => 5,
          'topic' => $progress->question_type->label(),
        ]);
      }
    }
  }

  /**
   * Get all available badge types with descriptions.
   */
  public function getAllBadgeTypes(): array
  {
    return [
      // Completion Badges
      'first_quiz' => [
        'name' => 'First Steps',
        'description' => 'Completed your first quiz',
        'icon' => '🎯',
        'category' => 'completion',
      ],
      'quiz_milestone_5' => [
        'name' => 'Getting Started',
        'description' => 'Completed 5 quizzes',
        'icon' => '🏃',
        'category' => 'completion',
      ],
      'quiz_milestone_10' => [
        'name' => 'Dedicated Learner',
        'description' => 'Completed 10 quizzes',
        'icon' => '📚',
        'category' => 'completion',
      ],
      'quiz_milestone_25' => [
        'name' => 'Quiz Enthusiast',
        'description' => 'Completed 25 quizzes',
        'icon' => '🎓',
        'category' => 'completion',
      ],
      'quiz_milestone_50' => [
        'name' => 'Quiz Master',
        'description' => 'Completed 50 quizzes',
        'icon' => '👑',
        'category' => 'completion',
      ],
      'quiz_milestone_100' => [
        'name' => 'Quiz Legend',
        'description' => 'Completed 100 quizzes',
        'icon' => '🏆',
        'category' => 'completion',
      ],

      // Accuracy Badges
      'perfect_score' => [
        'name' => 'Perfect Score',
        'description' => 'Scored 100% on a quiz',
        'icon' => '⭐',
        'category' => 'accuracy',
      ],
      'accuracy_achiever' => [
        'name' => 'Accuracy Achiever',
        'description' => 'Achieved 85% accuracy',
        'icon' => '🎯',
        'category' => 'accuracy',
      ],
      'accuracy_master' => [
        'name' => 'Accuracy Master',
        'description' => 'Achieved 90% accuracy',
        'icon' => '🏹',
        'category' => 'accuracy',
      ],
      'accuracy_expert' => [
        'name' => 'Accuracy Expert',
        'description' => 'Achieved 95% accuracy',
        'icon' => '🎪',
        'category' => 'accuracy',
      ],
      'consistent_accuracy' => [
        'name' => 'Consistent Performer',
        'description' => '5 quizzes in a row with 80%+ accuracy',
        'icon' => '🔥',
        'category' => 'accuracy',
      ],

      // Speed Badges
      'quick_thinker' => [
        'name' => 'Quick Thinker',
        'description' => 'Average under 15 seconds per question',
        'icon' => '⚡',
        'category' => 'speed',
      ],
      'speed_demon' => [
        'name' => 'Speed Demon',
        'description' => 'Average under 10 seconds per question',
        'icon' => '🏎️',
        'category' => 'speed',
      ],
      'lightning_fast' => [
        'name' => 'Lightning Fast',
        'description' => 'Under 5 seconds per question with 90%+ accuracy',
        'icon' => '⚡',
        'category' => 'speed',
      ],

      // Streak Badges
      'streak_starter' => [
        'name' => 'Streak Starter',
        'description' => '3 correct answers in a row',
        'icon' => '🔥',
        'category' => 'streak',
      ],
      'streak_achiever' => [
        'name' => 'Streak Achiever',
        'description' => '5 correct answers in a row',
        'icon' => '🔥',
        'category' => 'streak',
      ],
      'streak_champion' => [
        'name' => 'Streak Champion',
        'description' => '7 correct answers in a row',
        'icon' => '🔥',
        'category' => 'streak',
      ],
      'streak_master' => [
        'name' => 'Streak Master',
        'description' => '10 correct answers in a row',
        'icon' => '🔥',
        'category' => 'streak',
      ],

      // Daily Streak Badges
      'daily_streak_starter' => [
        'name' => 'Daily Dedication',
        'description' => 'Practiced 3 days in a row',
        'icon' => '📅',
        'category' => 'daily_streak',
      ],
      'daily_streak_achiever' => [
        'name' => 'Weekly Warrior',
        'description' => 'Practiced 7 days in a row',
        'icon' => '📅',
        'category' => 'daily_streak',
      ],
      'daily_streak_champion' => [
        'name' => 'Fortnight Fighter',
        'description' => 'Practiced 14 days in a row',
        'icon' => '📅',
        'category' => 'daily_streak',
      ],
      'daily_streak_legend' => [
        'name' => 'Monthly Master',
        'description' => 'Practiced 30 days in a row',
        'icon' => '📅',
        'category' => 'daily_streak',
      ],

      // Mastery Badges
      'mastery_achiever' => [
        'name' => 'Topic Achiever',
        'description' => 'Reached 80% mastery level',
        'icon' => '🎖️',
        'category' => 'mastery',
      ],
      'mastery_master' => [
        'name' => 'Topic Master',
        'description' => 'Reached 85% mastery level',
        'icon' => '🏅',
        'category' => 'mastery',
      ],
      'mastery_expert' => [
        'name' => 'Topic Expert',
        'description' => 'Reached 90% mastery level',
        'icon' => '🥇',
        'category' => 'mastery',
      ],
      'mastery_legend' => [
        'name' => 'Topic Legend',
        'description' => 'Reached 95% mastery level',
        'icon' => '👑',
        'category' => 'mastery',
      ],

      // Point Badges
      'point_starter' => [
        'name' => 'Point Collector',
        'description' => 'Earned 100 points',
        'icon' => '💎',
        'category' => 'points',
      ],
      'point_collector' => [
        'name' => 'Point Gatherer',
        'description' => 'Earned 250 points',
        'icon' => '💎',
        'category' => 'points',
      ],
      'point_achiever' => [
        'name' => 'Point Achiever',
        'description' => 'Earned 500 points',
        'icon' => '💎',
        'category' => 'points',
      ],
      'point_champion' => [
        'name' => 'Point Champion',
        'description' => 'Earned 1,000 points',
        'icon' => '💎',
        'category' => 'points',
      ],
      'point_master' => [
        'name' => 'Point Master',
        'description' => 'Earned 2,000 points',
        'icon' => '💎',
        'category' => 'points',
      ],
      'point_legend' => [
        'name' => 'Point Legend',
        'description' => 'Earned 5,000 points',
        'icon' => '💎',
        'category' => 'points',
      ],
    ];
  }

  /**
   * Get badge information by type.
   */
  public function getBadgeInfo(string $badgeType): ?array
  {
    $badges = $this->getAllBadgeTypes();
    return $badges[$badgeType] ?? null;
  }
}
