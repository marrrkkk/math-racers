<?php

namespace Tests\Unit\Services;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\User;
use App\Services\ScoringService;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
  use RefreshDatabase;

  private ScoringService $scoringService;
  private User $student;
  private User $teacher;

  protected function setUp(): void
  {
    parent::setUp();

    $this->scoringService = new ScoringService();

    $this->student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $this->teacher = User::factory()->create([
      'role' => UserRole::TEACHER,
    ]);
  }

  public function test_calculates_base_points_correctly(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'correct_answers' => 7,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateBasePoints');
    $method->setAccessible(true);

    $basePoints = $method->invoke($this->scoringService, $session);

    $this->assertEquals(70, $basePoints); // 7 correct * 10 points each
  }

  public function test_calculates_accuracy_bonus_correctly(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'correct_answers' => 8,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateAccuracyBonus');
    $method->setAccessible(true);

    $accuracyBonus = $method->invoke($this->scoringService, $session);

    $this->assertEquals(40, $accuracyBonus); // 80% accuracy * 50 max bonus
  }

  public function test_calculates_time_bonus_for_fast_completion(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'time_taken' => 200, // 200 seconds (optimal is 300)
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateTimeBonus');
    $method->setAccessible(true);

    $timeBonus = $method->invoke($this->scoringService, $session);

    $this->assertEquals(10, $timeBonus); // (300-200)/10 = 10 bonus points
  }

  public function test_no_time_bonus_for_slow_completion(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'time_taken' => 400, // 400 seconds (over optimal 300)
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateTimeBonus');
    $method->setAccessible(true);

    $timeBonus = $method->invoke($this->scoringService, $session);

    $this->assertEquals(0, $timeBonus);
  }

  public function test_calculates_difficulty_bonus(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
    ]);

    // Create questions with different difficulties
    $easyQuestion = Question::factory()->create([
      'difficulty' => Difficulty::EASY,
      'created_by' => $this->teacher->id,
    ]);

    $hardQuestion = Question::factory()->create([
      'difficulty' => Difficulty::HARD,
      'created_by' => $this->teacher->id,
    ]);

    // Create correct answers for both questions
    QuizAnswer::factory()->create([
      'quiz_session_id' => $session->id,
      'question_id' => $easyQuestion->id,
      'is_correct' => true,
    ]);

    QuizAnswer::factory()->create([
      'quiz_session_id' => $session->id,
      'question_id' => $hardQuestion->id,
      'is_correct' => true,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateDifficultyBonus');
    $method->setAccessible(true);

    $difficultyBonus = $method->invoke($this->scoringService, $session);

    // Easy (5) + Hard (15) = 20 points
    $this->assertEquals(20, $difficultyBonus);
  }

  public function test_calculates_full_quiz_score(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 5,
      'correct_answers' => 4,
      'time_taken' => 120, // Under optimal time (150)
    ]);

    // Create questions and answers
    $questions = Question::factory()
      ->count(5)
      ->create([
        'difficulty' => Difficulty::MEDIUM,
        'created_by' => $this->teacher->id,
      ]);

    // Create 4 correct answers and 1 incorrect
    for ($i = 0; $i < 4; $i++) {
      QuizAnswer::factory()->create([
        'quiz_session_id' => $session->id,
        'question_id' => $questions[$i]->id,
        'is_correct' => true,
      ]);
    }

    QuizAnswer::factory()->create([
      'quiz_session_id' => $session->id,
      'question_id' => $questions[4]->id,
      'is_correct' => false,
    ]);

    $totalScore = $this->scoringService->calculateQuizScore($session);

    // Base: 4 * 10 = 40
    // Accuracy: 0.8 * 50 = 40
    // Time: (150-120)/10 = 3
    // Difficulty: 4 * 10 = 40 (medium questions)
    // Total: 40 + 40 + 3 + 40 = 123
    $this->assertEquals(123, $totalScore);
  }

  public function test_calculates_individual_answer_score(): void
  {
    $question = Question::factory()->create([
      'difficulty' => Difficulty::HARD,
      'created_by' => $this->teacher->id,
    ]);

    $correctAnswer = QuizAnswer::factory()->create([
      'question_id' => $question->id,
      'is_correct' => true,
      'time_taken' => 8, // Fast answer
    ]);

    $score = $this->scoringService->calculateAnswerScore($correctAnswer);

    // Hard question (15) + quick answer bonus (5) = 20
    $this->assertEquals(20, $score);
  }

  public function test_incorrect_answer_gets_zero_score(): void
  {
    $question = Question::factory()->create([
      'difficulty' => Difficulty::HARD,
      'created_by' => $this->teacher->id,
    ]);

    $incorrectAnswer = QuizAnswer::factory()->create([
      'question_id' => $question->id,
      'is_correct' => false,
      'time_taken' => 5,
    ]);

    $score = $this->scoringService->calculateAnswerScore($incorrectAnswer);

    $this->assertEquals(0, $score);
  }

  public function test_calculates_streak_bonus(): void
  {
    $answers = [
      ['is_correct' => true],
      ['is_correct' => true],
      ['is_correct' => true],
      ['is_correct' => true],
      ['is_correct' => false],
      ['is_correct' => true],
      ['is_correct' => true],
      ['is_correct' => true],
    ];

    $streakBonus = $this->scoringService->calculateStreakBonus($answers);

    // Max streak is 4, so (4-2) * 5 = 10 points
    $this->assertEquals(10, $streakBonus);
  }

  public function test_no_streak_bonus_for_short_streaks(): void
  {
    $answers = [
      ['is_correct' => true],
      ['is_correct' => true],
      ['is_correct' => false],
      ['is_correct' => true],
      ['is_correct' => false],
    ];

    $streakBonus = $this->scoringService->calculateStreakBonus($answers);

    $this->assertEquals(0, $streakBonus); // Max streak is 2, no bonus
  }

  public function test_grade_multipliers(): void
  {
    $this->assertEquals(1.0, $this->scoringService->getGradeMultiplier(1));
    $this->assertEquals(1.1, $this->scoringService->getGradeMultiplier(2));
    $this->assertEquals(1.2, $this->scoringService->getGradeMultiplier(3));
    $this->assertEquals(1.0, $this->scoringService->getGradeMultiplier(4)); // Default
  }

  public function test_performance_rating_excellent(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'correct_answers' => 9,
      'points_earned' => 180,
    ]);

    $rating = $this->scoringService->getPerformanceRating($session);

    $this->assertEquals('Excellent', $rating['rating']);
    $this->assertEquals(5, $rating['stars']);
    $this->assertEquals(90, $rating['accuracy']);
    $this->assertEquals(18, $rating['points_per_question']);
  }

  public function test_performance_rating_needs_improvement(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 10,
      'correct_answers' => 4,
      'points_earned' => 50,
    ]);

    $rating = $this->scoringService->getPerformanceRating($session);

    $this->assertEquals('Needs Improvement', $rating['rating']);
    $this->assertEquals(1, $rating['stars']);
    $this->assertEquals(40, $rating['accuracy']);
    $this->assertEquals(5, $rating['points_per_question']);
  }

  public function test_points_to_next_level_beginner(): void
  {
    $levelInfo = $this->scoringService->getPointsToNextLevel(50);

    $this->assertEquals('Beginner', $levelInfo['current_level']['name']);
    $this->assertEquals('Learner', $levelInfo['next_level']['name']);
    $this->assertEquals(50, $levelInfo['points_needed']); // 100 - 50
    $this->assertEquals(50, $levelInfo['progress_percentage']); // 50/100 * 100
  }

  public function test_points_to_next_level_max_level(): void
  {
    $levelInfo = $this->scoringService->getPointsToNextLevel(6000);

    $this->assertEquals('Champion', $levelInfo['current_level']['name']);
    $this->assertNull($levelInfo['next_level']);
    $this->assertEquals(0, $levelInfo['points_needed']);
    $this->assertEquals(100, $levelInfo['progress_percentage']);
  }

  public function test_answer_time_bonus_quick(): void
  {
    $answer = QuizAnswer::factory()->create([
      'is_correct' => true,
      'time_taken' => 8,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateAnswerTimeBonus');
    $method->setAccessible(true);

    $timeBonus = $method->invoke($this->scoringService, $answer);

    $this->assertEquals(5, $timeBonus); // Quick answer bonus
  }

  public function test_answer_time_bonus_moderate(): void
  {
    $answer = QuizAnswer::factory()->create([
      'is_correct' => true,
      'time_taken' => 15,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateAnswerTimeBonus');
    $method->setAccessible(true);

    $timeBonus = $method->invoke($this->scoringService, $answer);

    $this->assertEquals(2, $timeBonus); // Moderate speed bonus
  }

  public function test_answer_time_bonus_slow(): void
  {
    $answer = QuizAnswer::factory()->create([
      'is_correct' => true,
      'time_taken' => 25,
    ]);

    $reflection = new \ReflectionClass($this->scoringService);
    $method = $reflection->getMethod('calculateAnswerTimeBonus');
    $method->setAccessible(true);

    $timeBonus = $method->invoke($this->scoringService, $answer);

    $this->assertEquals(0, $timeBonus); // No bonus for slow answers
  }
}
