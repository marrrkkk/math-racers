<?php

namespace Tests\Unit\Services;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\User;
use App\Services\QuizService;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizServiceTest extends TestCase
{
  use RefreshDatabase;

  private QuizService $quizService;
  private User $student;
  private User $teacher;

  protected function setUp(): void
  {
    parent::setUp();

    $this->quizService = new QuizService();

    $this->student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $this->teacher = User::factory()->create([
      'role' => UserRole::TEACHER,
    ]);
  }

  public function test_can_create_quiz_session_for_student(): void
  {
    $session = $this->quizService->createQuizSession(
      $this->student,
      QuestionType::ADDITION,
      2,
      10
    );

    $this->assertInstanceOf(QuizSession::class, $session);
    $this->assertEquals($this->student->id, $session->student_id);
    $this->assertEquals(QuestionType::ADDITION, $session->question_type);
    $this->assertEquals(2, $session->grade_level);
    $this->assertEquals(10, $session->total_questions);
    $this->assertEquals(0, $session->correct_answers);
    $this->assertEquals(0, $session->points_earned);
  }

  public function test_cannot_create_quiz_session_for_non_student(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Only students can take quizzes');

    $this->quizService->createQuizSession(
      $this->teacher,
      QuestionType::ADDITION,
      2,
      10
    );
  }

  public function test_cannot_create_quiz_session_with_mismatched_grade(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Grade level must match student\'s grade');

    $this->quizService->createQuizSession(
      $this->student,
      QuestionType::ADDITION,
      3, // Student is grade 2, requesting grade 3
      10
    );
  }

  public function test_can_get_questions_for_quiz(): void
  {
    // Create test questions
    Question::factory()
      ->count(15)
      ->create([
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'created_by' => $this->teacher->id,
      ]);

    $questions = $this->quizService->getQuestionsForQuiz(
      QuestionType::ADDITION,
      2,
      10
    );

    $this->assertCount(10, $questions);
    $questions->each(function ($question) {
      $this->assertEquals(QuestionType::ADDITION, $question->question_type);
      $this->assertEquals(2, $question->grade_level);
    });
  }

  public function test_throws_exception_when_not_enough_questions(): void
  {
    // Create only 5 questions when 10 are requested
    Question::factory()
      ->count(5)
      ->create([
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'created_by' => $this->teacher->id,
      ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Not enough questions available');

    $this->quizService->getQuestionsForQuiz(
      QuestionType::ADDITION,
      2,
      10
    );
  }

  public function test_can_submit_correct_answer(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'correct_answers' => 0,
    ]);

    $question = Question::factory()->create([
      'question_text' => '5 + 3 = ?',
      'correct_answer' => '8',
      'created_by' => $this->teacher->id,
    ]);

    $answer = $this->quizService->submitAnswer(
      $session,
      $question,
      '8',
      15
    );

    $this->assertTrue($answer->is_correct);
    $this->assertEquals('8', $answer->student_answer);
    $this->assertEquals(15, $answer->time_taken);

    // Check that session was updated
    $session->refresh();
    $this->assertEquals(1, $session->correct_answers);
  }

  public function test_can_submit_incorrect_answer(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'correct_answers' => 0,
    ]);

    $question = Question::factory()->create([
      'question_text' => '5 + 3 = ?',
      'correct_answer' => '8',
      'created_by' => $this->teacher->id,
    ]);

    $answer = $this->quizService->submitAnswer(
      $session,
      $question,
      '7',
      20
    );

    $this->assertFalse($answer->is_correct);
    $this->assertEquals('7', $answer->student_answer);

    // Check that session correct_answers wasn't incremented
    $session->refresh();
    $this->assertEquals(0, $session->correct_answers);
  }

  public function test_can_complete_quiz_session(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'total_questions' => 5,
      'correct_answers' => 4,
      'completed_at' => null,
    ]);

    $completedSession = $this->quizService->completeQuizSession($session, 120);

    $this->assertNotNull($completedSession->completed_at);
    $this->assertEquals(120, $completedSession->time_taken);
    $this->assertGreaterThan(0, $completedSession->points_earned);
  }

  public function test_can_get_student_quiz_history(): void
  {
    // Create completed quiz sessions
    QuizSession::factory()
      ->count(5)
      ->create([
        'student_id' => $this->student->id,
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'completed_at' => now(),
      ]);

    // Create sessions for different type (should not be included)
    QuizSession::factory()
      ->count(3)
      ->create([
        'student_id' => $this->student->id,
        'question_type' => QuestionType::SUBTRACTION,
        'grade_level' => 2,
        'completed_at' => now(),
      ]);

    $history = $this->quizService->getStudentQuizHistory(
      $this->student,
      QuestionType::ADDITION,
      2,
      10
    );

    $this->assertCount(5, $history);
    $history->each(function ($session) {
      $this->assertEquals($this->student->id, $session->student_id);
      $this->assertEquals(QuestionType::ADDITION, $session->question_type);
      $this->assertEquals(2, $session->grade_level);
      $this->assertNotNull($session->completed_at);
    });
  }

  public function test_can_get_student_best_performance(): void
  {
    // Create quiz sessions with different scores
    $sessions = QuizSession::factory()
      ->count(3)
      ->create([
        'student_id' => $this->student->id,
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'completed_at' => now(),
      ]);

    // Set different points for each session
    $sessions[0]->update(['points_earned' => 50]);
    $sessions[1]->update(['points_earned' => 80]); // This should be the best
    $sessions[2]->update(['points_earned' => 60]);

    $bestPerformance = $this->quizService->getStudentBestPerformance(
      $this->student,
      QuestionType::ADDITION,
      2
    );

    $this->assertEquals(80, $bestPerformance->points_earned);
  }

  public function test_can_get_student_average_stats(): void
  {
    // Create quiz sessions with known values
    QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'points_earned' => 80,
      'time_taken' => 120,
      'total_questions' => 10,
      'correct_answers' => 8,
      'completed_at' => now(),
    ]);

    QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'points_earned' => 60,
      'time_taken' => 180,
      'total_questions' => 10,
      'correct_answers' => 6,
      'completed_at' => now(),
    ]);

    $stats = $this->quizService->getStudentAverageStats(
      $this->student,
      QuestionType::ADDITION,
      2
    );

    $this->assertEquals(70, $stats['average_accuracy']); // (80+60)/2
    $this->assertEquals(70, $stats['average_points']); // (80+60)/2
    $this->assertEquals(150, $stats['average_time']); // (120+180)/2
    $this->assertEquals(2, $stats['total_sessions']);
  }

  public function test_returns_empty_stats_for_no_sessions(): void
  {
    $stats = $this->quizService->getStudentAverageStats(
      $this->student,
      QuestionType::ADDITION,
      2
    );

    $this->assertEquals(0, $stats['average_accuracy']);
    $this->assertEquals(0, $stats['average_points']);
    $this->assertEquals(0, $stats['average_time']);
    $this->assertEquals(0, $stats['total_sessions']);
  }

  public function test_can_validate_active_session(): void
  {
    $activeSession = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => null,
      'created_at' => now()->subMinutes(30), // 30 minutes ago
    ]);

    $this->assertTrue($this->quizService->isValidSession($activeSession));
  }

  public function test_invalid_session_when_completed(): void
  {
    $completedSession = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => now(),
    ]);

    $this->assertFalse($this->quizService->isValidSession($completedSession));
  }

  public function test_invalid_session_when_too_old(): void
  {
    $oldSession = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => null,
      'created_at' => now()->subHours(2), // 2 hours ago
    ]);

    $this->assertFalse($this->quizService->isValidSession($oldSession));
  }

  public function test_can_abandon_quiz_session(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => null,
      'points_earned' => 50,
    ]);

    $this->quizService->abandonQuizSession($session);

    $session->refresh();
    $this->assertNotNull($session->completed_at);
    $this->assertEquals(0, $session->points_earned);
  }

  public function test_cannot_abandon_completed_session(): void
  {
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => now(),
      'points_earned' => 80,
    ]);

    $originalCompletedAt = $session->completed_at;
    $originalPoints = $session->points_earned;

    $this->quizService->abandonQuizSession($session);

    $session->refresh();
    $this->assertEquals($originalCompletedAt, $session->completed_at);
    $this->assertEquals($originalPoints, $session->points_earned);
  }
}
