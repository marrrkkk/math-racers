<?php

namespace Tests\Feature;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
  use RefreshDatabase;

  private User $teacher;

  protected function setUp(): void
  {
    parent::setUp();

    $this->teacher = User::factory()->create([
      'role' => UserRole::TEACHER,
    ]);
  }

  public function test_question_bank_has_questions_for_all_grades_and_types(): void
  {
    // Run the seeder
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    // Check that we have questions for each grade and type combination
    for ($grade = 1; $grade <= 3; $grade++) {
      foreach (QuestionType::cases() as $type) {
        $count = Question::where('grade_level', $grade)
          ->where('question_type', $type)
          ->count();

        $this->assertGreaterThan(
          0,
          $count,
          "No questions found for Grade {$grade} {$type->label()}"
        );
      }
    }
  }

  public function test_questions_have_valid_deped_competencies(): void
  {
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    $questions = Question::all();

    $this->assertGreaterThan(0, $questions->count());

    foreach ($questions as $question) {
      $this->assertNotEmpty($question->deped_competency);
      $this->assertGreaterThan(10, strlen($question->deped_competency)); // Should be descriptive
    }
  }

  public function test_questions_have_correct_answers(): void
  {
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    $questions = Question::all();

    foreach ($questions as $question) {
      $this->assertNotEmpty($question->correct_answer);
      $this->assertTrue(is_numeric($question->correct_answer) || ctype_digit($question->correct_answer));
    }
  }

  public function test_grade_1_questions_are_appropriate_difficulty(): void
  {
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    $grade1Questions = Question::where('grade_level', 1)->get();

    foreach ($grade1Questions as $question) {
      // Check that Grade 1 questions use smaller numbers
      if ($question->question_type === QuestionType::ADDITION) {
        preg_match('/(\d+)\s*\+\s*(\d+)/', $question->question_text, $matches);
        if (!empty($matches)) {
          $this->assertLessThanOrEqual(20, (int)$matches[1]);
          $this->assertLessThanOrEqual(20, (int)$matches[2]);
        }
      }
    }
  }

  public function test_grade_3_questions_are_more_complex(): void
  {
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    $grade3Questions = Question::where('grade_level', 3)->get();

    foreach ($grade3Questions as $question) {
      // Check that Grade 3 questions use larger numbers
      if ($question->question_type === QuestionType::ADDITION) {
        preg_match('/(\d+)\s*\+\s*(\d+)/', $question->question_text, $matches);
        if (!empty($matches)) {
          $this->assertGreaterThanOrEqual(100, (int)$matches[1]);
        }
      }
    }
  }

  public function test_questions_can_be_answered_correctly(): void
  {
    $this->artisan('db:seed', ['--class' => 'QuestionSeeder']);

    $questions = Question::limit(10)->get();

    foreach ($questions as $question) {
      // Test that the stored correct answer is actually correct
      $this->assertTrue($question->isCorrectAnswer($question->correct_answer));

      // Test that a wrong answer is rejected
      $wrongAnswer = (string)((int)$question->correct_answer + 1);
      $this->assertFalse($question->isCorrectAnswer($wrongAnswer));
    }
  }

  public function test_question_factory_creates_valid_questions(): void
  {
    $questions = Question::factory()
      ->count(5)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    foreach ($questions as $question) {
      $this->assertInstanceOf(Question::class, $question);
      $this->assertNotEmpty($question->question_text);
      $this->assertNotEmpty($question->correct_answer);
      $this->assertTrue($question->isCorrectAnswer($question->correct_answer));
      $this->assertBetween($question->grade_level, 1, 3);
    }
  }

  public function test_can_create_questions_for_specific_requirements(): void
  {
    // Test creating questions for specific grade and type
    $grade2AdditionQuestions = Question::factory()
      ->count(5)
      ->forGrade(2)
      ->ofType(QuestionType::ADDITION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    foreach ($grade2AdditionQuestions as $question) {
      $this->assertEquals(2, $question->grade_level);
      $this->assertEquals(QuestionType::ADDITION, $question->question_type);
      $this->assertStringContainsString('+', $question->question_text);
    }
  }

  public function test_question_difficulty_affects_points(): void
  {
    $easyQuestion = Question::factory()
      ->easy()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $hardQuestion = Question::factory()
      ->hard()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertLessThan($hardQuestion->points, $easyQuestion->points);
  }

  public function test_multiple_choice_questions_have_options(): void
  {
    $mcQuestion = Question::factory()
      ->multipleChoice()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertTrue($mcQuestion->isMultipleChoice());
    $this->assertIsArray($mcQuestion->options);
    $this->assertCount(4, $mcQuestion->options);
  }

  private function assertBetween($value, $min, $max): void
  {
    $this->assertGreaterThanOrEqual($min, $value);
    $this->assertLessThanOrEqual($max, $value);
  }
}
