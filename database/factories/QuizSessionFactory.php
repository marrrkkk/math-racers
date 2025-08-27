<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizSession>
 */
class QuizSessionFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $totalQuestions = $this->faker->numberBetween(5, 15);
    $correctAnswers = $this->faker->numberBetween(0, $totalQuestions);
    $accuracy = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

    return [
      'student_id' => User::factory()->create(['role' => UserRole::STUDENT, 'grade_level' => $this->faker->numberBetween(1, 3)]),
      'question_type' => $this->faker->randomElement(QuestionType::cases()),
      'grade_level' => $this->faker->numberBetween(1, 3),
      'total_questions' => $totalQuestions,
      'correct_answers' => $correctAnswers,
      'points_earned' => $this->faker->numberBetween(0, $totalQuestions * 20),
      'time_taken' => $this->faker->numberBetween(60, 600), // 1-10 minutes
      'accuracy' => round($accuracy, 2),
      'completed_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
    ];
  }

  /**
   * Create a completed quiz session.
   */
  public function completed(): static
  {
    return $this->state(fn() => [
      'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
    ]);
  }

  /**
   * Create an active (incomplete) quiz session.
   */
  public function active(): static
  {
    return $this->state(fn() => [
      'completed_at' => null,
      'points_earned' => 0,
      'time_taken' => 0,
    ]);
  }

  /**
   * Create a quiz session for a specific student.
   */
  public function forStudent(User $student): static
  {
    return $this->state(fn() => [
      'student_id' => $student->id,
      'grade_level' => $student->grade_level,
    ]);
  }

  /**
   * Create a quiz session of a specific type.
   */
  public function ofType(QuestionType $type): static
  {
    return $this->state(fn() => [
      'question_type' => $type,
    ]);
  }

  /**
   * Create a quiz session for a specific grade.
   */
  public function forGrade(int $grade): static
  {
    return $this->state(fn() => [
      'grade_level' => $grade,
    ]);
  }

  /**
   * Create a high-scoring quiz session.
   */
  public function highScore(): static
  {
    return $this->state(function () {
      $totalQuestions = $this->faker->numberBetween(8, 12);
      $correctAnswers = $this->faker->numberBetween(7, $totalQuestions);
      $accuracy = ($correctAnswers / $totalQuestions) * 100;

      return [
        'total_questions' => $totalQuestions,
        'correct_answers' => $correctAnswers,
        'accuracy' => round($accuracy, 2),
        'points_earned' => $this->faker->numberBetween(120, 200),
        'time_taken' => $this->faker->numberBetween(120, 300),
        'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
      ];
    });
  }

  /**
   * Create a low-scoring quiz session.
   */
  public function lowScore(): static
  {
    return $this->state(function () {
      $totalQuestions = $this->faker->numberBetween(8, 12);
      $correctAnswers = $this->faker->numberBetween(0, 4);
      $accuracy = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

      return [
        'total_questions' => $totalQuestions,
        'correct_answers' => $correctAnswers,
        'accuracy' => round($accuracy, 2),
        'points_earned' => $this->faker->numberBetween(0, 50),
        'time_taken' => $this->faker->numberBetween(300, 600),
        'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
      ];
    });
  }

  /**
   * Create a perfect score quiz session.
   */
  public function perfectScore(): static
  {
    return $this->state(function () {
      $totalQuestions = $this->faker->numberBetween(8, 12);

      return [
        'total_questions' => $totalQuestions,
        'correct_answers' => $totalQuestions,
        'accuracy' => 100.00,
        'points_earned' => $this->faker->numberBetween(150, 250),
        'time_taken' => $this->faker->numberBetween(60, 200),
        'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
      ];
    });
  }

  /**
   * Create a recent quiz session.
   */
  public function recent(): static
  {
    return $this->state(fn() => [
      'created_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
      'completed_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
    ]);
  }

  /**
   * Create an old quiz session.
   */
  public function old(): static
  {
    return $this->state(fn() => [
      'created_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
      'completed_at' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
    ]);
  }
}
