<?php

namespace Tests\Feature;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionManagementTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    // Create a teacher user for testing
    $this->teacher = User::factory()->create([
      'role' => UserRole::TEACHER,
    ]);
  }

  public function test_teacher_can_view_questions_index()
  {
    $this->actingAs($this->teacher)
      ->get(route('teacher.questions.index'))
      ->assertStatus(200)
      ->assertInertia(
        fn($page) => $page
          ->component('Teacher/Questions/Index')
          ->has('questions')
          ->has('questionTypes')
          ->has('difficulties')
          ->has('gradeLevels')
      );
  }

  public function test_teacher_can_view_create_question_form()
  {
    $this->actingAs($this->teacher)
      ->get(route('teacher.questions.create'))
      ->assertStatus(200)
      ->assertInertia(
        fn($page) => $page
          ->component('Teacher/Questions/Create')
          ->has('questionTypes')
          ->has('difficulties')
          ->has('gradeLevels')
          ->has('depedCompetencies')
      );
  }

  public function test_teacher_can_create_question()
  {
    $questionData = [
      'question_text' => 'What is 2 + 2?',
      'question_type' => 'addition',
      'grade_level' => 1,
      'difficulty' => 'easy',
      'correct_answer' => '4',
      'deped_competency' => 'Add whole numbers up to 100 without regrouping',
    ];

    $this->actingAs($this->teacher)
      ->post(route('teacher.questions.store'), $questionData)
      ->assertRedirect(route('teacher.questions.index'))
      ->assertSessionHas('success');

    $this->assertDatabaseHas('questions', [
      'question_text' => 'What is 2 + 2?',
      'question_type' => 'addition',
      'grade_level' => 1,
      'difficulty' => 'easy',
      'correct_answer' => '4',
      'created_by' => $this->teacher->id,
    ]);
  }

  public function test_teacher_can_create_multiple_choice_question()
  {
    $questionData = [
      'question_text' => 'What is 3 + 5?',
      'question_type' => 'addition',
      'grade_level' => 2,
      'difficulty' => 'medium',
      'correct_answer' => '8',
      'options' => ['6', '7', '8', '9'],
      'deped_competency' => 'Add 2- to 3-digit numbers without regrouping',
    ];

    $this->actingAs($this->teacher)
      ->post(route('teacher.questions.store'), $questionData)
      ->assertRedirect(route('teacher.questions.index'));

    $this->assertDatabaseHas('questions', [
      'question_text' => 'What is 3 + 5?',
      'correct_answer' => '8',
      'options' => json_encode(['6', '7', '8', '9']),
    ]);
  }

  public function test_teacher_can_view_question_details()
  {
    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $this->actingAs($this->teacher)
      ->get(route('teacher.questions.show', $question))
      ->assertStatus(200)
      ->assertInertia(
        fn($page) => $page
          ->component('Teacher/Questions/Show')
          ->has('question')
      );
  }

  public function test_teacher_can_edit_question()
  {
    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $this->actingAs($this->teacher)
      ->get(route('teacher.questions.edit', $question))
      ->assertStatus(200)
      ->assertInertia(
        fn($page) => $page
          ->component('Teacher/Questions/Edit')
          ->has('question')
          ->has('questionTypes')
          ->has('difficulties')
          ->has('gradeLevels')
          ->has('depedCompetencies')
      );
  }

  public function test_teacher_can_update_question()
  {
    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
      'question_text' => 'Original question',
    ]);

    $updateData = [
      'question_text' => 'Updated question text',
      'question_type' => $question->question_type->value,
      'grade_level' => $question->grade_level,
      'difficulty' => $question->difficulty->value,
      'correct_answer' => $question->correct_answer,
      'deped_competency' => $question->deped_competency,
    ];

    $this->actingAs($this->teacher)
      ->put(route('teacher.questions.update', $question), $updateData)
      ->assertRedirect(route('teacher.questions.index'));

    $this->assertDatabaseHas('questions', [
      'id' => $question->id,
      'question_text' => 'Updated question text',
    ]);
  }

  public function test_teacher_can_delete_unused_question()
  {
    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $this->actingAs($this->teacher)
      ->delete(route('teacher.questions.destroy', $question))
      ->assertRedirect(route('teacher.questions.index'));

    $this->assertDatabaseMissing('questions', [
      'id' => $question->id,
    ]);
  }

  public function test_validation_errors_on_invalid_question_data()
  {
    $invalidData = [
      'question_text' => '', // Required field empty
      'question_type' => 'invalid_type',
      'grade_level' => 5, // Out of range
      'difficulty' => 'invalid_difficulty',
      'correct_answer' => '',
      'deped_competency' => '',
    ];

    $this->actingAs($this->teacher)
      ->post(route('teacher.questions.store'), $invalidData)
      ->assertSessionHasErrors([
        'question_text',
        'question_type',
        'grade_level',
        'difficulty',
        'correct_answer',
        'deped_competency',
      ]);
  }

  public function test_non_teacher_cannot_access_question_management()
  {
    $student = User::factory()->create([
      'role' => UserRole::STUDENT,
    ]);

    $this->actingAs($student)
      ->get(route('teacher.questions.index'))
      ->assertStatus(403);
  }
}
