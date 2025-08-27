<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $questionType = $this->faker->randomElement(QuestionType::cases());
    $gradeLevel = $this->faker->numberBetween(1, 3);
    $difficulty = $this->faker->randomElement(Difficulty::cases());

    // Generate question and calculate answer based on the actual math
    $questionData = $this->generateQuestionWithAnswer($questionType, $gradeLevel);

    return [
      'question_text' => $questionData['question_text'],
      'question_type' => $questionType,
      'grade_level' => $gradeLevel,
      'difficulty' => $difficulty,
      'correct_answer' => $questionData['correct_answer'],
      'options' => $this->faker->boolean(30) ? $this->generateOptions() : null,
      'deped_competency' => $this->generateCompetency($questionType, $gradeLevel),
      'created_by' => User::factory()->create(['role' => UserRole::TEACHER]),
    ];
  }



  /**
   * Generate a question with both text and correct answer.
   */
  private function generateQuestionWithAnswer(QuestionType $type, int $grade): array
  {
    return match ($type) {
      QuestionType::ADDITION => $this->generateAdditionQuestionWithAnswer($grade),
      QuestionType::SUBTRACTION => $this->generateSubtractionQuestionWithAnswer($grade),
      QuestionType::MULTIPLICATION => $this->generateMultiplicationQuestionWithAnswer($grade),
      QuestionType::DIVISION => $this->generateDivisionQuestionWithAnswer($grade),
    };
  }

  /**
   * Generate a question text based on type and grade level.
   */
  private function generateQuestionText(QuestionType $type, int $grade): string
  {
    $questionData = $this->generateQuestionWithAnswer($type, $grade);
    return $questionData['question_text'];
  }

  /**
   * Generate addition question based on grade level
   */
  private function generateAdditionQuestion(int $grade): string
  {
    return match ($grade) {
      1 => $this->generateGrade1Addition(),
      2 => $this->generateGrade2Addition(),
      3 => $this->generateGrade3Addition(),
      default => $this->generateGrade1Addition(),
    };
  }

  /**
   * Generate subtraction question based on grade level
   */
  private function generateSubtractionQuestion(int $grade): string
  {
    return match ($grade) {
      1 => $this->generateGrade1Subtraction(),
      2 => $this->generateGrade2Subtraction(),
      3 => $this->generateGrade3Subtraction(),
      default => $this->generateGrade1Subtraction(),
    };
  }

  /**
   * Generate multiplication question based on grade level
   */
  private function generateMultiplicationQuestion(int $grade): string
  {
    return match ($grade) {
      1 => $this->generateGrade1Multiplication(),
      2 => $this->generateGrade2Multiplication(),
      3 => $this->generateGrade3Multiplication(),
      default => $this->generateGrade1Multiplication(),
    };
  }

  /**
   * Generate division question based on grade level
   */
  private function generateDivisionQuestion(int $grade): string
  {
    return match ($grade) {
      1 => $this->generateGrade1Division(),
      2 => $this->generateGrade2Division(),
      3 => $this->generateGrade3Division(),
      default => $this->generateGrade1Division(),
    };
  }

  // Addition question generators with answers
  private function generateAdditionQuestionWithAnswer(int $grade): array
  {
    return match ($grade) {
      1 => $this->generateGrade1AdditionWithAnswer(),
      2 => $this->generateGrade2AdditionWithAnswer(),
      3 => $this->generateGrade3AdditionWithAnswer(),
      default => $this->generateGrade1AdditionWithAnswer(),
    };
  }

  private function generateSubtractionQuestionWithAnswer(int $grade): array
  {
    return match ($grade) {
      1 => $this->generateGrade1SubtractionWithAnswer(),
      2 => $this->generateGrade2SubtractionWithAnswer(),
      3 => $this->generateGrade3SubtractionWithAnswer(),
      default => $this->generateGrade1SubtractionWithAnswer(),
    };
  }

  private function generateMultiplicationQuestionWithAnswer(int $grade): array
  {
    return match ($grade) {
      1 => $this->generateGrade1MultiplicationWithAnswer(),
      2 => $this->generateGrade2MultiplicationWithAnswer(),
      3 => $this->generateGrade3MultiplicationWithAnswer(),
      default => $this->generateGrade1MultiplicationWithAnswer(),
    };
  }

  private function generateDivisionQuestionWithAnswer(int $grade): array
  {
    return match ($grade) {
      1 => $this->generateGrade1DivisionWithAnswer(),
      2 => $this->generateGrade2DivisionWithAnswer(),
      3 => $this->generateGrade3DivisionWithAnswer(),
      default => $this->generateGrade1DivisionWithAnswer(),
    };
  }

  // Grade 1 question generators with answers
  private function generateGrade1AdditionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(1, 10);
    $num2 = $this->faker->numberBetween(1, 10);
    $answer = $num1 + $num2;
    return [
      'question_text' => "{$num1} + {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade1SubtractionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(5, 20);
    $num2 = $this->faker->numberBetween(1, $num1);
    $answer = $num1 - $num2;
    return [
      'question_text' => "{$num1} - {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade1MultiplicationWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(1, 5);
    $num2 = $this->faker->numberBetween(1, 5);
    $answer = $num1 * $num2;
    return [
      'question_text' => "{$num1} × {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade1DivisionWithAnswer(): array
  {
    $divisor = $this->faker->numberBetween(2, 5);
    $quotient = $this->faker->numberBetween(1, 5);
    $dividend = $divisor * $quotient;
    return [
      'question_text' => "{$dividend} ÷ {$divisor} = ?",
      'correct_answer' => (string) $quotient,
    ];
  }

  // Grade 2 question generators with answers
  private function generateGrade2AdditionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(10, 99);
    $num2 = $this->faker->numberBetween(10, 99);
    $answer = $num1 + $num2;
    return [
      'question_text' => "{$num1} + {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade2SubtractionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(20, 99);
    $num2 = $this->faker->numberBetween(10, $num1);
    $answer = $num1 - $num2;
    return [
      'question_text' => "{$num1} - {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade2MultiplicationWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(10, 25);
    $num2 = $this->faker->numberBetween(2, 9);
    $answer = $num1 * $num2;
    return [
      'question_text' => "{$num1} × {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade2DivisionWithAnswer(): array
  {
    $divisor = $this->faker->numberBetween(2, 9);
    $quotient = $this->faker->numberBetween(10, 25);
    $dividend = $divisor * $quotient;
    return [
      'question_text' => "{$dividend} ÷ {$divisor} = ?",
      'correct_answer' => (string) $quotient,
    ];
  }

  // Grade 3 question generators with answers
  private function generateGrade3AdditionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(100, 999);
    $num2 = $this->faker->numberBetween(100, 999);
    $answer = $num1 + $num2;
    return [
      'question_text' => "{$num1} + {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade3SubtractionWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(200, 999);
    $num2 = $this->faker->numberBetween(100, $num1);
    $answer = $num1 - $num2;
    return [
      'question_text' => "{$num1} - {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade3MultiplicationWithAnswer(): array
  {
    $num1 = $this->faker->numberBetween(100, 999);
    $num2 = $this->faker->numberBetween(2, 9);
    $answer = $num1 * $num2;
    return [
      'question_text' => "{$num1} × {$num2} = ?",
      'correct_answer' => (string) $answer,
    ];
  }

  private function generateGrade3DivisionWithAnswer(): array
  {
    $divisor = $this->faker->numberBetween(2, 9);
    $quotient = $this->faker->numberBetween(100, 999);
    $dividend = $divisor * $quotient;
    return [
      'question_text' => "{$dividend} ÷ {$divisor} = ?",
      'correct_answer' => (string) $quotient,
    ];
  }

  /**
   * Generate multiple choice options.
   */
  private function generateOptions(): array
  {
    return [
      $this->faker->numberBetween(1, 50),
      $this->faker->numberBetween(51, 100),
      $this->faker->numberBetween(101, 150),
      $this->faker->numberBetween(151, 200),
    ];
  }

  /**
   * Generate a DepEd competency based on type and grade.
   */
  private function generateCompetency(QuestionType $type, int $grade): string
  {
    $competencies = [
      1 => [
        'addition' => 'Add whole numbers up to 100 without regrouping',
        'subtraction' => 'Subtract whole numbers up to 100 without regrouping',
        'multiplication' => 'Visualize and represent multiplication of numbers 1-10',
        'division' => 'Visualize and represent division of numbers 1-10',
      ],
      2 => [
        'addition' => 'Add 2- to 3-digit numbers without regrouping',
        'subtraction' => 'Subtract 2- to 3-digit numbers without regrouping',
        'multiplication' => 'Multiply 2-digit by 1-digit numbers without regrouping',
        'division' => 'Divide 2- to 3-digit numbers by 1-digit numbers',
      ],
      3 => [
        'addition' => 'Add 3- to 4-digit numbers with regrouping',
        'subtraction' => 'Subtract 3- to 4-digit numbers with regrouping',
        'multiplication' => 'Multiply 3-digit by 1-digit numbers',
        'division' => 'Divide 3- to 4-digit numbers by 1-digit numbers',
      ],
    ];

    return $competencies[$grade][$type->value] ?? 'Basic math competency';
  }

  /**
   * Create an easy question.
   */
  public function easy(): static
  {
    return $this->state(fn() => [
      'difficulty' => Difficulty::EASY,
    ]);
  }

  /**
   * Create a medium question.
   */
  public function medium(): static
  {
    return $this->state(fn() => [
      'difficulty' => Difficulty::MEDIUM,
    ]);
  }

  /**
   * Create a hard question.
   */
  public function hard(): static
  {
    return $this->state(fn() => [
      'difficulty' => Difficulty::HARD,
    ]);
  }

  /**
   * Create a multiple choice question.
   */
  public function multipleChoice(): static
  {
    return $this->state(fn() => [
      'options' => $this->generateOptions(),
    ]);
  }

  /**
   * Create a question for a specific grade.
   */
  public function forGrade(int $grade): static
  {
    return $this->state(fn() => [
      'grade_level' => $grade,
    ]);
  }

  /**
   * Create a question of a specific type.
   */
  public function ofType(QuestionType $type): static
  {
    return $this->state(function (array $attributes) use ($type) {
      $grade = $attributes['grade_level'] ?? $this->faker->numberBetween(1, 3);
      $questionData = $this->generateQuestionWithAnswer($type, $grade);

      return [
        'question_type' => $type,
        'question_text' => $questionData['question_text'],
        'correct_answer' => $questionData['correct_answer'],
        'deped_competency' => $this->generateCompetency($type, $grade),
      ];
    });
  }
}
