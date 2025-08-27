<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuizSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizAnswer>
 */
class QuizAnswerFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $question = Question::factory()->create();
    $isCorrect = $this->faker->boolean(70); // 70% chance of correct answer

    return [
      'quiz_session_id' => QuizSession::factory(),
      'question_id' => $question->id,
      'student_answer' => $isCorrect ? $question->correct_answer : $this->faker->numberBetween(1, 100),
      'is_correct' => $isCorrect,
      'time_taken' => $this->faker->numberBetween(5, 60), // 5-60 seconds
    ];
  }

  /**
   * Create a correct answer.
   */
  public function correct(): static
  {
    return $this->state(function (array $attributes) {
      $question = Question::find($attributes['question_id']) ?? Question::factory()->create();

      return [
        'question_id' => $question->id,
        'student_answer' => $question->correct_answer,
        'is_correct' => true,
      ];
    });
  }

  /**
   * Create an incorrect answer.
   */
  public function incorrect(): static
  {
    return $this->state(function (array $attributes) {
      $question = Question::find($attributes['question_id']) ?? Question::factory()->create();
      $wrongAnswer = $this->generateWrongAnswer($question->correct_answer);

      return [
        'question_id' => $question->id,
        'student_answer' => $wrongAnswer,
        'is_correct' => false,
      ];
    });
  }

  /**
   * Create a quick answer (under 10 seconds).
   */
  public function quick(): static
  {
    return $this->state(fn() => [
      'time_taken' => $this->faker->numberBetween(3, 9),
    ]);
  }

  /**
   * Create a slow answer (over 30 seconds).
   */
  public function slow(): static
  {
    return $this->state(fn() => [
      'time_taken' => $this->faker->numberBetween(31, 60),
    ]);
  }

  /**
   * Create an answer for a specific quiz session.
   */
  public function forSession(QuizSession $session): static
  {
    return $this->state(fn() => [
      'quiz_session_id' => $session->id,
    ]);
  }

  /**
   * Create an answer for a specific question.
   */
  public function forQuestion(Question $question): static
  {
    return $this->state(fn() => [
      'question_id' => $question->id,
    ]);
  }

  /**
   * Create an answer with specific time taken.
   */
  public function withTime(int $seconds): static
  {
    return $this->state(fn() => [
      'time_taken' => $seconds,
    ]);
  }

  /**
   * Generate a wrong answer based on the correct answer.
   */
  private function generateWrongAnswer(string $correctAnswer): string
  {
    $correct = (int) $correctAnswer;

    // Generate a wrong answer that's close but not correct
    $wrongAnswers = [
      $correct + 1,
      $correct - 1,
      $correct + 2,
      $correct - 2,
      $correct * 2,
      (int) ($correct / 2),
    ];

    // Filter out the correct answer and negative numbers
    $wrongAnswers = array_filter($wrongAnswers, fn($answer) => $answer !== $correct && $answer > 0);

    if (empty($wrongAnswers)) {
      return (string) ($correct + 1);
    }

    return (string) $this->faker->randomElement($wrongAnswers);
  }
}
