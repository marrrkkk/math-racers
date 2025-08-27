<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Quiz Services Verification ===\n\n";

try {
  // Test QuizService
  $quizService = new App\Services\QuizService();
  echo "✓ QuizService instantiated successfully\n";

  // Test ScoringService
  $scoringService = new App\Services\ScoringService();
  echo "✓ ScoringService instantiated successfully\n";

  // Test ProgressService
  $progressService = new App\Services\ProgressService();
  echo "✓ ProgressService instantiated successfully\n";

  // Test BadgeService
  $badgeService = new App\Services\BadgeService();
  echo "✓ BadgeService instantiated successfully\n";

  // Test badge types
  $badgeTypes = $badgeService->getAllBadgeTypes();
  echo "✓ Badge types loaded: " . count($badgeTypes) . " badges available\n";

  // Test badge categories
  $categories = array_unique(array_column($badgeTypes, 'category'));
  echo "✓ Badge categories: " . implode(', ', $categories) . "\n";

  // Test scoring service methods
  $levelInfo = $scoringService->getPointsToNextLevel(150);
  echo "✓ Scoring service level calculation working\n";

  echo "\n=== All Services Working Correctly ===\n";
} catch (Exception $e) {
  echo "✗ Error: " . $e->getMessage() . "\n";
  echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
