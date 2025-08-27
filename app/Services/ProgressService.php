<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\QuizSession;
use App\Models\StudentProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProgressService
{
  /**
   * Update student progress after completing a quiz session.
   */
  public function updateProgressFromQuizSession(QuizSession $quizSession): StudentProgress
  {
    return DB::transaction(function () use ($quizSession) {
      // Get or create progress record
      $progress = StudentProgress::firstOrCreate([
        'student_id' => $quizSession->student_id,
        'question_type' => $quizSession->question_type,
        'grade_level' => $quizSession->grade_level,
      ], [
        'total_points' => 0,
        'badges_earned' => [],
        'mastery_level' => 0.0,
        'last_activity' => now(),
      ]);

      // Add points from this quiz
      $progress->addPoints($quizSession->points_earned);

      // Update mastery level based on recent performance
      $newMastery = $this->calculateMasteryLevel($quizSession->student, $quizSession->question_type, $quizSession->grade_level);
      $progress->updateMasteryLevel($newMastery);

      // Check and award badges
      $this->checkAndAwardBadges($progress, $quizSession);

      return $progress;
    });
  }

  /**
   * Calculate mastery level based on recent quiz performance.
   */
  public function calculateMasteryLevel(User $student, QuestionType $questionType, int $gradeLevel): float
  {
    // Get last 10 quiz sessions for this topic
    $recentSessions = QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->orderBy('completed_at', 'desc')
      ->limit(10)
      ->get();

    if ($recentSessions->isEmpty()) {
      return 0.0;
    }

    // Calculate weighted average (more recent sessions have higher weight)
    $totalWeight = 0;
    $weightedSum = 0;

    foreach ($recentSessions as $index => $session) {
      $weight = 10 - $index; // Most recent has weight 10, oldest has weight 1
      $weightedSum += $session->accuracy * $weight;
      $totalWeight += $weight;
    }

    return $totalWeight > 0 ? $weightedSum / $totalWeight : 0.0;
  }

  /**
   * Check and award badges based on performance.
   */
  protected function checkAndAwardBadges(StudentProgress $progress, QuizSession $quizSession): void
  {
    $badgeService = new BadgeService();

    // Check completion badges
    $badgeService->checkCompletionBadges($progress, $quizSession);

    // Check accuracy badges
    $badgeService->checkAccuracyBadges($progress, $quizSession);

    // Check speed badges
    $badgeService->checkSpeedBadges($progress, $quizSession);

    // Check streak badges
    $badgeService->checkStreakBadges($progress, $quizSession);

    // Check mastery badges
    $badgeService->checkMasteryBadges($progress);

    // Check point milestone badges
    $badgeService->checkPointMilestoneBadges($progress);
  }

  /**
   * Get comprehensive progress statistics for a student.
   */
  public function getStudentProgressStats(User $student, int $gradeLevel): array
  {
    $progressRecords = StudentProgress::forStudent($student->id)
      ->forGrade($gradeLevel)
      ->get();

    $stats = [
      'total_points' => 0,
      'total_badges' => 0,
      'topics_mastered' => 0,
      'average_mastery' => 0.0,
      'topics' => [],
      'recent_activity' => null,
    ];

    foreach ($progressRecords as $progress) {
      $stats['total_points'] += $progress->total_points;
      $stats['total_badges'] += $progress->total_badges;

      if ($progress->hasMastery()) {
        $stats['topics_mastered']++;
      }

      $stats['topics'][$progress->question_type->value] = [
        'points' => $progress->total_points,
        'mastery_level' => $progress->mastery_percentage,
        'mastery_category' => $progress->mastery_category,
        'badges' => $progress->total_badges,
        'rank' => $progress->rank,
        'last_activity' => $progress->last_activity,
      ];

      if (!$stats['recent_activity'] || $progress->last_activity > $stats['recent_activity']) {
        $stats['recent_activity'] = $progress->last_activity;
      }
    }

    // Calculate average mastery
    if ($progressRecords->isNotEmpty()) {
      $stats['average_mastery'] = $progressRecords->avg('mastery_level');
    }

    return $stats;
  }

  /**
   * Get leaderboard for a specific topic and grade level.
   */
  public function getLeaderboard(QuestionType $questionType, int $gradeLevel, int $limit = 10): array
  {
    $topStudents = StudentProgress::with('student')
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->orderBy('total_points', 'desc')
      ->orderBy('mastery_level', 'desc')
      ->limit($limit)
      ->get();

    return $topStudents->map(function ($progress, $index) {
      return [
        'rank' => $index + 1,
        'student_name' => $progress->student->name,
        'points' => $progress->total_points,
        'mastery_level' => $progress->mastery_percentage,
        'badges' => $progress->total_badges,
        'last_activity' => $progress->last_activity,
      ];
    })->toArray();
  }

  /**
   * Get class-wide progress summary for teachers.
   */
  public function getClassProgressSummary(array $studentIds, int $gradeLevel): array
  {
    $progressRecords = StudentProgress::with('student')
      ->whereIn('student_id', $studentIds)
      ->forGrade($gradeLevel)
      ->get();

    $summary = [
      'total_students' => count($studentIds),
      'active_students' => 0,
      'topics' => [],
      'overall_stats' => [
        'average_points' => 0,
        'average_mastery' => 0,
        'students_with_mastery' => 0,
      ],
    ];

    // Group by topic
    $topicGroups = $progressRecords->groupBy('question_type');

    foreach (QuestionType::cases() as $questionType) {
      $topicProgress = $topicGroups->get($questionType->value, collect());

      $summary['topics'][$questionType->value] = [
        'label' => $questionType->label(),
        'students_attempted' => $topicProgress->count(),
        'average_points' => $topicProgress->avg('total_points') ?? 0,
        'average_mastery' => $topicProgress->avg('mastery_level') ?? 0,
        'students_mastered' => $topicProgress->where('mastery_level', '>=', 80)->count(),
        'top_performer' => $this->getTopPerformer($topicProgress),
      ];
    }

    // Calculate overall stats
    if ($progressRecords->isNotEmpty()) {
      $summary['overall_stats']['average_points'] = round($progressRecords->avg('total_points'), 2);
      $summary['overall_stats']['average_mastery'] = round($progressRecords->avg('mastery_level'), 2);
      $summary['overall_stats']['students_with_mastery'] = $progressRecords->where('mastery_level', '>=', 80)->count();
    }

    // Count active students (activity in last 7 days)
    $weekAgo = now()->subWeek();
    $summary['active_students'] = $progressRecords->where('last_activity', '>=', $weekAgo)->count();

    return $summary;
  }

  /**
   * Get top performer for a topic.
   */
  protected function getTopPerformer($progressCollection): ?array
  {
    $topPerformer = $progressCollection
      ->sortByDesc('total_points')
      ->sortByDesc('mastery_level')
      ->first();

    if (!$topPerformer) {
      return null;
    }

    return [
      'name' => $topPerformer->student->name,
      'points' => $topPerformer->total_points,
      'mastery_level' => $topPerformer->mastery_percentage,
    ];
  }

  /**
   * Reset progress for a student (admin function).
   */
  public function resetStudentProgress(User $student, QuestionType $questionType, int $gradeLevel): void
  {
    StudentProgress::where('student_id', $student->id)
      ->where('question_type', $questionType)
      ->where('grade_level', $gradeLevel)
      ->delete();
  }

  /**
   * Get progress trends over time.
   */
  public function getProgressTrends(User $student, QuestionType $questionType, int $gradeLevel, int $days = 30): array
  {
    $startDate = now()->subDays($days);

    $sessions = QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->where('completed_at', '>=', $startDate)
      ->orderBy('completed_at')
      ->get();

    $trends = [];
    $cumulativePoints = 0;

    foreach ($sessions as $session) {
      $cumulativePoints += $session->points_earned;

      $trends[] = [
        'date' => $session->completed_at->format('Y-m-d'),
        'points_earned' => $session->points_earned,
        'cumulative_points' => $cumulativePoints,
        'accuracy' => $session->accuracy,
        'time_taken' => $session->time_taken,
      ];
    }

    return $trends;
  }
}
