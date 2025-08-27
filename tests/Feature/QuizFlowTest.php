<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
  use RefreshDatabase;

  private User $student;
  private User $teacher;

  protected function setUp(): void
  {
    parent::setUp();

    $this->student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $this->teacher = User::factory()->create([
      'role' => UserRole::TEACHER,
    ]);

    // Create sample questions
    Question::factory()
      ->count(20)
      ->create([
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'created_by' => $this->teacher->id,
      ]);
  }

  public function test_student_can_start_quiz(): void
  {
    $this->actingAs($this->student);

    $response = $this->post('/quiz/start', [
      'question_type' => 'addition',
      'grade_level' => 2,
      'total_questions' => 10,
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('quiz_sessions', [
      'student_id' => $this->student->id,
      'question_type' => 'addition',
      'grade_level' => 2,
      'total_questions' => 10,
      'completed_at' => null,
    ]);
  }

  public function test_student_cannot_start_quiz_for_wrong_grade(): void
  {
    $this->actingAs($this->student);

    $response = $this->post('/quiz/start', [
      'question_type' => 'addition',
      'grade_level' => 3, // Student is grade 2
      'total_questions' => 10,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['grade_level']);
  }

  public function test_teacher_cannot_start_quiz(): void
  {
    $this->actingAs($this->teacher);

    $response = $this->post('/quiz/start', [
      'question_type' => 'addition',
      'grade_level' => 2,
      'total_questions' => 10,
    ]);

    $response->assertStatus(403);
  }

  public function test_student_can_get_quiz_questions(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'total_questions' => 5,
    ]);

    $response = $this->get("/quiz/{$session->id}/questions");

    $response->assertStatus(200);
    $response->assertJsonStructure([
      'session' => [
        'id',
        'question_type',
        'grade_level',
        'total_questions',
      ],
      'questions' => [
        '*' => [
          'id',
          'question_text',
          'difficulty',
          'options',
        ]
      ]
    ]);

    $data = $response->json();
    $this->assertCount(5, $data['questions']);
  }

  public function test_student_can_submit_answer(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
    ]);

    $question = Question::factory()->create([
      'question_text' => '5 + 3 = ?',
      'correct_answer' => '8',
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'created_by' => $this->teacher->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => $question->id,
      'answer' => '8',
      'time_taken' => 15,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
      'correct' => true,
      'points_earned' => $question->points,
    ]);

    $this->assertDatabaseHas('quiz_answers', [
      'quiz_session_id' => $session->id,
      'question_id' => $question->id,
      'student_answer' => '8',
      'is_correct' => true,
      'time_taken' => 15,
    ]);
  }

  public function test_student_can_submit_incorrect_answer(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
    ]);

    $question = Question::factory()->create([
      'question_text' => '5 + 3 = ?',
      'correct_answer' => '8',
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'created_by' => $this->teacher->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => $question->id,
      'answer' => '7',
      'time_taken' => 20,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
      'correct' => false,
      'correct_answer' => '8',
      'points_earned' => 0,
    ]);

    $this->assertDatabaseHas('quiz_answers', [
      'quiz_session_id' => $session->id,
      'question_id' => $question->id,
      'student_answer' => '7',
      'is_correct' => false,
    ]);
  }

  public function test_student_cannot_submit_answer_to_other_students_session(): void
  {
    $otherStudent = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $otherStudent->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
    ]);

    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => $question->id,
      'answer' => '8',
      'time_taken' => 15,
    ]);

    $response->assertStatus(403);
  }

  public function test_student_can_complete_quiz(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'total_questions' => 5,
      'correct_answers' => 4,
    ]);

    $response = $this->post("/quiz/{$session->id}/complete", [
      'total_time' => 120,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
      'session' => [
        'id',
        'points_earned',
        'completed_at',
        'accuracy',
      ],
      'performance' => [
        'rating',
        'stars',
        'accuracy',
        'points_per_question',
      ],
      'progress_updated' => true,
    ]);

    $session->refresh();
    $this->assertNotNull($session->completed_at);
    $this->assertEquals(120, $session->time_taken);
    $this->assertGreaterThan(0, $session->points_earned);
  }

  public function test_student_can_view_quiz_results(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'completed_at' => now(),
      'points_earned' => 85,
      'total_questions' => 10,
      'correct_answers' => 8,
    ]);

    $response = $this->get("/quiz/{$session->id}/results");

    $response->assertStatus(200);
    $response->assertJsonStructure([
      'session' => [
        'id',
        'points_earned',
        'accuracy',
        'time_taken',
        'completed_at',
      ],
      'answers' => [
        '*' => [
          'question',
          'student_answer',
          'is_correct',
          'time_taken',
        ]
      ],
      'performance' => [
        'rating',
        'stars',
      ],
    ]);
  }

  public function test_student_can_view_quiz_history(): void
  {
    $this->actingAs($this->student);

    // Create completed quiz sessions
    QuizSession::factory()
      ->count(3)
      ->create([
        'student_id' => $this->student->id,
        'question_type' => QuestionType::ADDITION,
        'grade_level' => 2,
        'completed_at' => now(),
      ]);

    $response = $this->get('/quiz/history', [
      'question_type' => 'addition',
      'grade_level' => 2,
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
      'sessions' => [
        '*' => [
          'id',
          'points_earned',
          'accuracy',
          'completed_at',
        ]
      ],
      'statistics' => [
        'average_accuracy',
        'average_points',
        'total_sessions',
        'best_performance',
      ],
    ]);

    $data = $response->json();
    $this->assertCount(3, $data['sessions']);
  }

  public function test_student_can_abandon_quiz(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'completed_at' => null,
    ]);

    $response = $this->post("/quiz/{$session->id}/abandon");

    $response->assertStatus(200);
    $response->assertJson([
      'message' => 'Quiz session abandoned successfully',
    ]);

    $session->refresh();
    $this->assertNotNull($session->completed_at);
    $this->assertEquals(0, $session->points_earned);
  }

  public function test_quiz_validation_requires_authentication(): void
  {
    $response = $this->post('/quiz/start', [
      'question_type' => 'addition',
      'grade_level' => 2,
      'total_questions' => 10,
    ]);

    $response->assertStatus(302); // Redirect to login
  }

  public function test_quiz_start_validation(): void
  {
    $this->actingAs($this->student);

    $response = $this->post('/quiz/start', [
      'question_type' => 'invalid_type',
      'grade_level' => 5, // Invalid grade
      'total_questions' => 0, // Invalid count
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
      'question_type',
      'grade_level',
      'total_questions',
    ]);
  }

  public function test_answer_submission_validation(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => 999, // Non-existent question
      'answer' => '', // Empty answer
      'time_taken' => -5, // Invalid time
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([
      'question_id',
      'answer',
      'time_taken',
    ]);
  }

  public function test_cannot_access_completed_session(): void
  {
    $this->actingAs($this->student);

    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => now(),
    ]);

    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => $question->id,
      'answer' => '8',
      'time_taken' => 15,
    ]);

    $response->assertStatus(400);
    $response->assertJson([
      'error' => 'Quiz session is already completed',
    ]);
  }

  public function test_session_timeout_handling(): void
  {
    $this->actingAs($this->student);

    // Create an old session (over 1 hour ago)
    $session = QuizSession::factory()->create([
      'student_id' => $this->student->id,
      'completed_at' => null,
      'created_at' => now()->subHours(2),
    ]);

    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $response = $this->post("/quiz/{$session->id}/answer", [
      'question_id' => $question->id,
      'answer' => '8',
      'time_taken' => 15,
    ]);

    $response->assertStatus(400);
    $response->assertJson([
      'error' => 'Quiz session has expired',
    ]);
  }
}
