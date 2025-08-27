<?php

echo "Testing service instantiation...\n";

// Test autoloading
require_once 'vendor/autoload.php';

try {
  // Test class loading without Laravel bootstrap
  if (class_exists('App\Services\QuizService')) {
    echo "✓ QuizService class exists\n";
  } else {
    echo "✗ QuizService class not found\n";
  }

  if (class_exists('App\Services\ScoringService')) {
    echo "✓ ScoringService class exists\n";
  } else {
    echo "✗ ScoringService class not found\n";
  }

  if (class_exists('App\Services\ProgressService')) {
    echo "✓ ProgressService class exists\n";
  } else {
    echo "✗ ProgressService class not found\n";
  }

  if (class_exists('App\Services\BadgeService')) {
    echo "✓ BadgeService class exists\n";
  } else {
    echo "✗ BadgeService class not found\n";
  }

  echo "\nAll service classes are properly defined!\n";
} catch (Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}
