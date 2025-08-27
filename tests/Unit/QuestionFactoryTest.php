<?php

namespace Tests\Unit;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionFactoryTest extends TestCase
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

  public function test_factory_creates_valid_question(): void
  {
    $question = Question::factory()->create([
      'created_by' => $this->teacher->id,
    ]);

    $this->assertInstanceOf(Question::class, $question);
    $this->assertNotEmpty($question->question_text);
    $this->assertInstanceOf(QuestionType::class, $question->question_type);
    $this->assertInstanceOf(Difficulty::class, $question->difficulty);
    $this->assertIsInt($question->grade_level);
    $this->assertBetween($question->grade_level, 1, 3);
    $this->assertNotEmpty($question->correct_answer);
    $this->assertNotEmpty($question->deped_competency);
  }

  public function test_factory_creates_easy_question(): void
  {
    $question = Question::factory()
      ->easy()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(Difficulty::EASY, $question->difficulty);
  }

  public function test_factory_creates_medium_question(): void
  {
    $question = Question::factory()
      ->medium()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(Difficulty::MEDIUM, $question->difficulty);
  }

  public function test_factory_creates_hard_question(): void
  {
    $question = Question::factory()
      ->hard()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(Difficulty::HARD, $question->difficulty);
  }

  public function test_factory_creates_multiple_choice_question(): void
  {
    $question = Question::factory()
      ->multipleChoice()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertNotNull($question->options);
    $this->assertIsArray($question->options);
    $this->assertCount(4, $question->options);
  }

  public function test_factory_creates_question_for_specific_grade(): void
  {
    $question = Question::factory()
      ->forGrade(3)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(3, $question->grade_level);
  }

  public function test_factory_creates_question_of_specific_type(): void
  {
    $question = Question::factory()
      ->ofType(QuestionType::MULTIPLICATION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(QuestionType::MULTIPLICATION, $question->question_type);
    $this->assertStringContainsString('×', $question->question_text);
  }

  public function test_factory_generates_correct_grade_1_addition(): void
  {
    $question = Question::factory()
      ->forGrade(1)
      ->ofType(QuestionType::ADDITION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(1, $question->grade_level);
    $this->assertEquals(QuestionType::ADDITION, $question->question_type);

    // Check that numbers are appropriate for grade 1
    preg_match('/(\d+)\s*\+\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $this->assertNotEmpty($matches);
    $this->assertLessThanOrEqual(10, (int)$matches[1]);
    $this->assertLessThanOrEqual(10, (int)$matches[2]);
  }

  public function test_factory_generates_correct_grade_2_subtraction(): void
  {
    $question = Question::factory()
      ->forGrade(2)
      ->ofType(QuestionType::SUBTRACTION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(2, $question->grade_level);
    $this->assertEquals(QuestionType::SUBTRACTION, $question->question_type);

    // Check that numbers are appropriate for grade 2
    preg_match('/(\d+)\s*-\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $this->assertNotEmpty($matches);
    $this->assertGreaterThanOrEqual(20, (int)$matches[1]);
    $this->assertLessThanOrEqual(99, (int)$matches[1]);
  }

  public function test_factory_generates_correct_grade_3_multiplication(): void
  {
    $question = Question::factory()
      ->forGrade(3)
      ->ofType(QuestionType::MULTIPLICATION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertEquals(3, $question->grade_level);
    $this->assertEquals(QuestionType::MULTIPLICATION, $question->question_type);

    // Check that numbers are appropriate for grade 3
    preg_match('/(\d+)\s*×\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $this->assertNotEmpty($matches);
    $this->assertGreaterThanOrEqual(100, (int)$matches[1]);
    $this->assertLessThanOrEqual(999, (int)$matches[1]);
    $this->assertGreaterThanOrEqual(2, (int)$matches[2]);
    $this->assertLessThanOrEqual(9, (int)$matches[2]);
  }

  public function test_factory_calculates_correct_answer_for_addition(): void
  {
    $question = Question::factory()
      ->ofType(QuestionType::ADDITION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    preg_match('/(\d+)\s*\+\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $expectedAnswer = (int)$matches[1] + (int)$matches[2];

    $this->assertEquals((string)$expectedAnswer, $question->correct_answer);
  }

  public function test_factory_calculates_correct_answer_for_subtraction(): void
  {
    $question = Question::factory()
      ->ofType(QuestionType::SUBTRACTION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    preg_match('/(\d+)\s*-\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $expectedAnswer = (int)$matches[1] - (int)$matches[2];

    $this->assertEquals((string)$expectedAnswer, $question->correct_answer);
  }

  public function test_factory_calculates_correct_answer_for_multiplication(): void
  {
    $question = Question::factory()
      ->ofType(QuestionType::MULTIPLICATION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    preg_match('/(\d+)\s*×\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $expectedAnswer = (int)$matches[1] * (int)$matches[2];

    $this->assertEquals((string)$expectedAnswer, $question->correct_answer);
  }

  public function test_factory_calculates_correct_answer_for_division(): void
  {
    $question = Question::factory()
      ->ofType(QuestionType::DIVISION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    preg_match('/(\d+)\s*÷\s*(\d+)\s*=\s*\?/', $question->question_text, $matches);
    $expectedAnswer = (int)$matches[1] / (int)$matches[2];

    $this->assertEquals((string)$expectedAnswer, $question->correct_answer);
  }

  public function test_factory_generates_appropriate_deped_competency(): void
  {
    $question = Question::factory()
      ->forGrade(2)
      ->ofType(QuestionType::ADDITION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertStringContainsString('Add', $question->deped_competency);
    $this->assertStringContainsString('2-', $question->deped_competency);
  }

  public function test_factory_creates_batch_of_questions(): void
  {
    $questions = Question::factory()
      ->count(10)
      ->forGrade(1)
      ->ofType(QuestionType::ADDITION)
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $this->assertCount(10, $questions);

    $questions->each(function ($question) {
      $this->assertEquals(1, $question->grade_level);
      $this->assertEquals(QuestionType::ADDITION, $question->question_type);
      $this->assertEquals($this->teacher->id, $question->created_by);
    });
  }

  public function test_factory_creates_mixed_difficulty_questions(): void
  {
    $easyQuestions = Question::factory()
      ->count(3)
      ->easy()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $hardQuestions = Question::factory()
      ->count(3)
      ->hard()
      ->create([
        'created_by' => $this->teacher->id,
      ]);

    $easyQuestions->each(function ($question) {
      $this->assertEquals(Difficulty::EASY, $question->difficulty);
    });

    $hardQuestions->each(function ($question) {
      $this->assertEquals(Difficulty::HARD, $question->difficulty);
    });
  }

  public function test_question_model_methods(): void
  {
    $question = Question::factory()->create([
      'question_text' => '5 + 3 = ?',
      'correct_answer' => '8',
      'difficulty' => Difficulty::MEDIUM,
      'options' => ['6', '7', '8', '9'],
      'created_by' => $this->teacher->id,
    ]);

    // Test isCorrectAnswer method
    $this->assertTrue($question->isCorrectAnswer('8'));
    $this->assertTrue($question->isCorrectAnswer(' 8 ')); // Trimmed
    $this->assertFalse($question->isCorrectAnswer('7'));

    // Test points attribute
    $this->assertEquals(10, $question->points); // Medium difficulty = 10 points

    // Test isMultipleChoice method
    $this->assertTrue($question->isMultipleChoice());

    // Test formatted question attribute
    $this->assertEquals('5 + 3 = ?', $question->formatted_question);
  }

  private function assertBetween($value, $min, $max): void
  {
    $this->assertGreaterThanOrEqual($min, $value);
    $this->assertLessThanOrEqual($max, $value);
  }
}
