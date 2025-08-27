<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Models\User;
use App\Models\StudentProgress;
use App\Models\QuizSession;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProgressTestSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Create test students
    $students = [];
    for ($i = 1; $i <= 10; $i++) {
      $students[] = User::create([
        'name' => "Test Student {$i}",
        'email' => "student{$i}@test.com",
        'password' => Hash::make('password'),
        'role' => UserRole::STUDENT,
        'grade_level' => rand(1, 3),
      ]);
    }

    // Create sample progress and quiz sessions
    foreach ($students as $student) {
      $topics = ['addition', 'subtraction', 'multiplication', 'division'];

      foreach ($topics as $topic) {
        // Skip some topics for variety
        if (rand(1, 100) > 70) continue;

        // Create progress record
        $progress = StudentProgress::create([
          'student_id' => $student->id,
          'question_type' => $topic,
          'grade_level' => $student->grade_level,
          'total_points' => rand(50, 1000),
          'badges_earned' => $this->generateRandomBadges(),
          'mastery_level' => rand(40, 95),
          'last_activity' => now()->subDays(rand(0, 7)),
        ]);

        // Create some quiz sessions
        $numSessions = rand(3, 15);
        for ($j = 0; $j < $numSessions; $j++) {
          QuizSession::create([
            'student_id' => $student->id,
            'question_type' => $topic,
            'grade_level' => $student->grade_level,
            'total_questions' => 10,
            'correct_answers' => rand(5, 10),
            'points_earned' => rand(30, 100),
            'time_taken' => rand(120, 600),
            'completed_at' => now()->subDays(rand(0, 30)),
          ]);
        }
      }
    }
  }

  private function generateRandomBadges(): array
  {
    $possibleBadges = [
      'first_quiz',
      'quiz_milestone_5',
      'quiz_milestone_10',
      'perfect_score',
      'accuracy_achiever',
      'accuracy_master',
      'quick_thinker',
      'speed_demon',
      'streak_starter',
      'mastery_achiever',
      'point_collector',
      'point_achiever'
    ];

    $numBadges = rand(0, 5);
    $badges = [];

    for ($i = 0; $i < $numBadges; $i++) {
      $badgeType = $possibleBadges[array_rand($possibleBadges)];
      $badges[] = [
        'type' => $badgeType,
        'earned_at' => now()->subDays(rand(1, 30))->toISOString(),
        'data' => ['description' => 'Test badge']
      ];
    }

    return $badges;
  }
}
