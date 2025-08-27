<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\StudentProgress;
use App\UserRole;

try {
  echo "Testing Progress and Leaderboard functionality...\n";

  // Test getting a student user
  $student = User::where('role', UserRole::STUDENT)->first();

  if (!$student) {
    echo "No student users found. Please run the seeder first.\n";
    exit(1);
  }

  echo "Found student: " . $student->name . "\n";
  echo "Grade level: " . $student->grade_level . "\n";

  // Test getting progress data
  $progressData = $student->progress()
    ->where('grade_level', $student->grade_level)
    ->get();

  echo "Progress records found: " . $progressData->count() . "\n";

  // Test total points calculation
  $totalPoints = $student->total_points;
  echo "Total points: " . $totalPoints . "\n";

  // Test badges
  $allBadges = $student->all_badges;
  echo "Total badges: " . count($allBadges) . "\n";

  // Test recent sessions
  $recentSessions = $student->completedQuizSessions()
    ->where('grade_level', $student->grade_level)
    ->orderBy('completed_at', 'desc')
    ->limit(5)
    ->get();

  echo "Recent sessions: " . $recentSessions->count() . "\n";

  echo "All tests passed successfully!\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
  exit(1);
}
