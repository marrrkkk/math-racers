<?php

namespace Database\Seeders;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
  /**
   * DepEd K-12 Mathematics Competencies for Grades 1-3
   */
  private array $depedCompetencies = [
    1 => [
      'addition' => [
        'M1NS-Ia-26.1' => 'Add whole numbers up to 100 without regrouping',
        'M1NS-Ib-26.2' => 'Add 2-digit numbers and 1-digit numbers without regrouping',
        'M1NS-Ic-26.3' => 'Add 2-digit numbers and 2-digit numbers without regrouping',
      ],
      'subtraction' => [
        'M1NS-Id-27.1' => 'Subtract whole numbers up to 100 without regrouping',
        'M1NS-Ie-27.2' => 'Subtract 1-digit numbers from 2-digit numbers without regrouping',
        'M1NS-If-27.3' => 'Subtract 2-digit numbers from 2-digit numbers without regrouping',
      ],
      'multiplication' => [
        'M1NS-Ig-28.1' => 'Visualize and represent multiplication of numbers 1-10',
        'M1NS-Ih-28.2' => 'Use concrete objects to show multiplication',
        'M1NS-Ii-28.3' => 'Write multiplication sentences using pictures and symbols',
      ],
      'division' => [
        'M1NS-Ij-29.1' => 'Visualize and represent division of numbers 1-10',
        'M1NS-Ik-29.2' => 'Use concrete objects to show equal sharing',
        'M1NS-Il-29.3' => 'Write division sentences using pictures and symbols',
      ],
    ],
    2 => [
      'addition' => [
        'M2NS-IIa-30.1' => 'Add 2- to 3-digit numbers without regrouping',
        'M2NS-IIb-30.2' => 'Add 2- to 3-digit numbers with regrouping',
        'M2NS-IIc-30.3' => 'Solve routine and non-routine problems involving addition',
      ],
      'subtraction' => [
        'M2NS-IId-31.1' => 'Subtract 2- to 3-digit numbers without regrouping',
        'M2NS-IIe-31.2' => 'Subtract 2- to 3-digit numbers with regrouping',
        'M2NS-IIf-31.3' => 'Solve routine and non-routine problems involving subtraction',
      ],
      'multiplication' => [
        'M2NS-IIg-32.1' => 'Multiply 2-digit by 1-digit numbers without regrouping',
        'M2NS-IIh-32.2' => 'Multiply 2-digit by 1-digit numbers with regrouping',
        'M2NS-IIi-32.3' => 'Solve routine and non-routine problems involving multiplication',
      ],
      'division' => [
        'M2NS-IIj-33.1' => 'Divide 2- to 3-digit numbers by 1-digit numbers',
        'M2NS-IIk-33.2' => 'Find the quotient of 2- to 3-digit numbers by 1-digit numbers',
        'M2NS-IIl-33.3' => 'Solve routine and non-routine problems involving division',
      ],
    ],
    3 => [
      'addition' => [
        'M3NS-IIIa-34.1' => 'Add 3- to 4-digit numbers with regrouping',
        'M3NS-IIIb-34.2' => 'Estimate sums of 3- to 4-digit numbers',
        'M3NS-IIIc-34.3' => 'Solve multi-step problems involving addition',
      ],
      'subtraction' => [
        'M3NS-IIId-35.1' => 'Subtract 3- to 4-digit numbers with regrouping',
        'M3NS-IIIe-35.2' => 'Estimate differences of 3- to 4-digit numbers',
        'M3NS-IIIf-35.3' => 'Solve multi-step problems involving subtraction',
      ],
      'multiplication' => [
        'M3NS-IIIg-36.1' => 'Multiply 3-digit by 1-digit numbers',
        'M3NS-IIIh-36.2' => 'Multiply 2-digit by 2-digit numbers',
        'M3NS-IIIi-36.3' => 'Solve multi-step problems involving multiplication',
      ],
      'division' => [
        'M3NS-IIIj-37.1' => 'Divide 3- to 4-digit numbers by 1-digit numbers',
        'M3NS-IIIk-37.2' => 'Divide 2- to 3-digit numbers by 2-digit numbers',
        'M3NS-IIIl-37.3' => 'Solve multi-step problems involving division',
      ],
    ],
  ];

  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get a teacher user to assign as creator, or create one
    $teacher = User::where('role', 'teacher')->first();
    if (!$teacher) {
      $teacher = User::factory()->create([
        'name' => 'Sample Teacher',
        'email' => 'teacher@example.com',
        'role' => 'teacher'
      ]);
    }

    // Generate comprehensive question bank with proper DepEd competencies
    $this->seedGrade1Questions($teacher);
    $this->seedGrade2Questions($teacher);
    $this->seedGrade3Questions($teacher);
  }

  /**
   * Seed Grade 1 questions with proper DepEd competencies
   */
  private function seedGrade1Questions(User $teacher): void
  {
    $questions = [
      // Grade 1 Addition - Easy (M1NS-Ia-26.1)
      ['question_text' => '2 + 3 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ia-26.1'],
      ['question_text' => '1 + 4 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ia-26.1'],
      ['question_text' => '6 + 2 = ?', 'correct_answer' => '8', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ia-26.1'],
      ['question_text' => '3 + 5 = ?', 'correct_answer' => '8', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ia-26.1'],
      ['question_text' => '4 + 4 = ?', 'correct_answer' => '8', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ia-26.1'],

      // Grade 1 Addition - 2-digit + 1-digit (M1NS-Ib-26.2)
      ['question_text' => '12 + 3 = ?', 'correct_answer' => '15', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ib-26.2'],
      ['question_text' => '14 + 5 = ?', 'correct_answer' => '19', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ib-26.2'],
      ['question_text' => '16 + 2 = ?', 'correct_answer' => '18', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ib-26.2'],
      ['question_text' => '11 + 7 = ?', 'correct_answer' => '18', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ib-26.2'],
      ['question_text' => '13 + 4 = ?', 'correct_answer' => '17', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ib-26.2'],

      // Grade 1 Subtraction - Easy (M1NS-Id-27.1)
      ['question_text' => '8 - 3 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Id-27.1'],
      ['question_text' => '9 - 4 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Id-27.1'],
      ['question_text' => '7 - 2 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Id-27.1'],
      ['question_text' => '10 - 6 = ?', 'correct_answer' => '4', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Id-27.1'],
      ['question_text' => '6 - 3 = ?', 'correct_answer' => '3', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Id-27.1'],

      // Grade 1 Subtraction - 2-digit minus 1-digit (M1NS-Ie-27.2)
      ['question_text' => '15 - 3 = ?', 'correct_answer' => '12', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ie-27.2'],
      ['question_text' => '18 - 5 = ?', 'correct_answer' => '13', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ie-27.2'],
      ['question_text' => '17 - 4 = ?', 'correct_answer' => '13', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ie-27.2'],
      ['question_text' => '19 - 7 = ?', 'correct_answer' => '12', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ie-27.2'],
      ['question_text' => '16 - 2 = ?', 'correct_answer' => '14', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M1NS-Ie-27.2'],

      // Grade 1 Multiplication - Visualization (M1NS-Ig-28.1)
      ['question_text' => '2 × 3 = ?', 'correct_answer' => '6', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ig-28.1'],
      ['question_text' => '2 × 4 = ?', 'correct_answer' => '8', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ig-28.1'],
      ['question_text' => '3 × 2 = ?', 'correct_answer' => '6', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ig-28.1'],
      ['question_text' => '2 × 5 = ?', 'correct_answer' => '10', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ig-28.1'],
      ['question_text' => '4 × 2 = ?', 'correct_answer' => '8', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ig-28.1'],

      // Grade 1 Division - Equal sharing (M1NS-Ij-29.1)
      ['question_text' => '6 ÷ 2 = ?', 'correct_answer' => '3', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ij-29.1'],
      ['question_text' => '8 ÷ 2 = ?', 'correct_answer' => '4', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ij-29.1'],
      ['question_text' => '10 ÷ 2 = ?', 'correct_answer' => '5', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ij-29.1'],
      ['question_text' => '9 ÷ 3 = ?', 'correct_answer' => '3', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ij-29.1'],
      ['question_text' => '12 ÷ 3 = ?', 'correct_answer' => '4', 'difficulty' => Difficulty::EASY, 'competency' => 'M1NS-Ij-29.1'],
    ];

    $this->createQuestions($questions, 1, QuestionType::ADDITION, $teacher, 0, 10);
    $this->createQuestions($questions, 1, QuestionType::SUBTRACTION, $teacher, 10, 10);
    $this->createQuestions($questions, 1, QuestionType::MULTIPLICATION, $teacher, 20, 5);
    $this->createQuestions($questions, 1, QuestionType::DIVISION, $teacher, 25, 5);
  }

  /**
   * Seed Grade 2 questions with proper DepEd competencies
   */
  private function seedGrade2Questions(User $teacher): void
  {
    $questions = [
      // Grade 2 Addition - Without regrouping (M2NS-IIa-30.1)
      ['question_text' => '23 + 14 = ?', 'correct_answer' => '37', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIa-30.1'],
      ['question_text' => '45 + 32 = ?', 'correct_answer' => '77', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIa-30.1'],
      ['question_text' => '156 + 23 = ?', 'correct_answer' => '179', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIa-30.1'],
      ['question_text' => '234 + 45 = ?', 'correct_answer' => '279', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIa-30.1'],
      ['question_text' => '67 + 21 = ?', 'correct_answer' => '88', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIa-30.1'],

      // Grade 2 Addition - With regrouping (M2NS-IIb-30.2)
      ['question_text' => '28 + 17 = ?', 'correct_answer' => '45', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIb-30.2'],
      ['question_text' => '39 + 26 = ?', 'correct_answer' => '65', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIb-30.2'],
      ['question_text' => '47 + 38 = ?', 'correct_answer' => '85', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIb-30.2'],
      ['question_text' => '156 + 78 = ?', 'correct_answer' => '234', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIb-30.2'],
      ['question_text' => '89 + 67 = ?', 'correct_answer' => '156', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIb-30.2'],

      // Grade 2 Subtraction - Without regrouping (M2NS-IId-31.1)
      ['question_text' => '78 - 23 = ?', 'correct_answer' => '55', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IId-31.1'],
      ['question_text' => '89 - 34 = ?', 'correct_answer' => '55', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IId-31.1'],
      ['question_text' => '156 - 23 = ?', 'correct_answer' => '133', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IId-31.1'],
      ['question_text' => '234 - 12 = ?', 'correct_answer' => '222', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IId-31.1'],
      ['question_text' => '67 - 45 = ?', 'correct_answer' => '22', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IId-31.1'],

      // Grade 2 Subtraction - With regrouping (M2NS-IIe-31.2)
      ['question_text' => '52 - 28 = ?', 'correct_answer' => '24', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIe-31.2'],
      ['question_text' => '73 - 39 = ?', 'correct_answer' => '34', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIe-31.2'],
      ['question_text' => '91 - 47 = ?', 'correct_answer' => '44', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIe-31.2'],
      ['question_text' => '156 - 89 = ?', 'correct_answer' => '67', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIe-31.2'],
      ['question_text' => '234 - 78 = ?', 'correct_answer' => '156', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIe-31.2'],

      // Grade 2 Multiplication - 2-digit by 1-digit without regrouping (M2NS-IIg-32.1)
      ['question_text' => '12 × 3 = ?', 'correct_answer' => '36', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIg-32.1'],
      ['question_text' => '21 × 4 = ?', 'correct_answer' => '84', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIg-32.1'],
      ['question_text' => '13 × 2 = ?', 'correct_answer' => '26', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIg-32.1'],
      ['question_text' => '11 × 5 = ?', 'correct_answer' => '55', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIg-32.1'],
      ['question_text' => '22 × 3 = ?', 'correct_answer' => '66', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIg-32.1'],

      // Grade 2 Multiplication - With regrouping (M2NS-IIh-32.2)
      ['question_text' => '15 × 4 = ?', 'correct_answer' => '60', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIh-32.2'],
      ['question_text' => '18 × 3 = ?', 'correct_answer' => '54', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIh-32.2'],
      ['question_text' => '16 × 5 = ?', 'correct_answer' => '80', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIh-32.2'],
      ['question_text' => '19 × 2 = ?', 'correct_answer' => '38', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIh-32.2'],
      ['question_text' => '17 × 4 = ?', 'correct_answer' => '68', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M2NS-IIh-32.2'],

      // Grade 2 Division - Basic division (M2NS-IIj-33.1)
      ['question_text' => '84 ÷ 4 = ?', 'correct_answer' => '21', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIj-33.1'],
      ['question_text' => '63 ÷ 3 = ?', 'correct_answer' => '21', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIj-33.1'],
      ['question_text' => '48 ÷ 2 = ?', 'correct_answer' => '24', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIj-33.1'],
      ['question_text' => '55 ÷ 5 = ?', 'correct_answer' => '11', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIj-33.1'],
      ['question_text' => '72 ÷ 6 = ?', 'correct_answer' => '12', 'difficulty' => Difficulty::EASY, 'competency' => 'M2NS-IIj-33.1'],
    ];

    $this->createQuestions($questions, 2, QuestionType::ADDITION, $teacher, 0, 10);
    $this->createQuestions($questions, 2, QuestionType::SUBTRACTION, $teacher, 10, 10);
    $this->createQuestions($questions, 2, QuestionType::MULTIPLICATION, $teacher, 20, 10);
    $this->createQuestions($questions, 2, QuestionType::DIVISION, $teacher, 30, 5);
  }

  /**
   * Seed Grade 3 questions with proper DepEd competencies
   */
  private function seedGrade3Questions(User $teacher): void
  {
    $questions = [
      // Grade 3 Addition - 3-4 digit with regrouping (M3NS-IIIa-34.1)
      ['question_text' => '1,456 + 2,789 = ?', 'correct_answer' => '4245', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIa-34.1'],
      ['question_text' => '2,345 + 1,678 = ?', 'correct_answer' => '4023', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIa-34.1'],
      ['question_text' => '789 + 456 = ?', 'correct_answer' => '1245', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIa-34.1'],
      ['question_text' => '567 + 789 = ?', 'correct_answer' => '1356', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIa-34.1'],
      ['question_text' => '1,234 + 567 = ?', 'correct_answer' => '1801', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIa-34.1'],

      // Grade 3 Addition - Estimation (M3NS-IIIb-34.2)
      ['question_text' => '198 + 203 = ? (estimate to nearest hundred)', 'correct_answer' => '400', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIb-34.2'],
      ['question_text' => '456 + 234 = ? (estimate to nearest hundred)', 'correct_answer' => '700', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIb-34.2'],
      ['question_text' => '789 + 123 = ? (estimate to nearest hundred)', 'correct_answer' => '900', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIb-34.2'],
      ['question_text' => '345 + 567 = ? (estimate to nearest hundred)', 'correct_answer' => '900', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIb-34.2'],
      ['question_text' => '678 + 234 = ? (estimate to nearest hundred)', 'correct_answer' => '900', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIb-34.2'],

      // Grade 3 Subtraction - 3-4 digit with regrouping (M3NS-IIId-35.1)
      ['question_text' => '4,567 - 1,789 = ?', 'correct_answer' => '2778', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIId-35.1'],
      ['question_text' => '3,456 - 1,234 = ?', 'correct_answer' => '2222', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIId-35.1'],
      ['question_text' => '1,000 - 456 = ?', 'correct_answer' => '544', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIId-35.1'],
      ['question_text' => '2,345 - 789 = ?', 'correct_answer' => '1556', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIId-35.1'],
      ['question_text' => '5,000 - 1,234 = ?', 'correct_answer' => '3766', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIId-35.1'],

      // Grade 3 Subtraction - Estimation (M3NS-IIIe-35.2)
      ['question_text' => '789 - 234 = ? (estimate to nearest hundred)', 'correct_answer' => '600', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIe-35.2'],
      ['question_text' => '567 - 123 = ? (estimate to nearest hundred)', 'correct_answer' => '400', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIe-35.2'],
      ['question_text' => '890 - 345 = ? (estimate to nearest hundred)', 'correct_answer' => '500', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIe-35.2'],
      ['question_text' => '678 - 234 = ? (estimate to nearest hundred)', 'correct_answer' => '400', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIe-35.2'],
      ['question_text' => '456 - 123 = ? (estimate to nearest hundred)', 'correct_answer' => '300', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIe-35.2'],

      // Grade 3 Multiplication - 3-digit by 1-digit (M3NS-IIIg-36.1)
      ['question_text' => '234 × 5 = ?', 'correct_answer' => '1170', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIg-36.1'],
      ['question_text' => '156 × 7 = ?', 'correct_answer' => '1092', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIg-36.1'],
      ['question_text' => '123 × 8 = ?', 'correct_answer' => '984', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIg-36.1'],
      ['question_text' => '345 × 4 = ?', 'correct_answer' => '1380', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIg-36.1'],
      ['question_text' => '278 × 6 = ?', 'correct_answer' => '1668', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIg-36.1'],

      // Grade 3 Multiplication - 2-digit by 2-digit (M3NS-IIIh-36.2)
      ['question_text' => '23 × 45 = ?', 'correct_answer' => '1035', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIh-36.2'],
      ['question_text' => '34 × 56 = ?', 'correct_answer' => '1904', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIh-36.2'],
      ['question_text' => '12 × 34 = ?', 'correct_answer' => '408', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIh-36.2'],
      ['question_text' => '25 × 16 = ?', 'correct_answer' => '400', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIh-36.2'],
      ['question_text' => '18 × 27 = ?', 'correct_answer' => '486', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIh-36.2'],

      // Grade 3 Division - 3-4 digit by 1-digit (M3NS-IIIj-37.1)
      ['question_text' => '1,248 ÷ 8 = ?', 'correct_answer' => '156', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIj-37.1'],
      ['question_text' => '2,145 ÷ 5 = ?', 'correct_answer' => '429', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIj-37.1'],
      ['question_text' => '936 ÷ 6 = ?', 'correct_answer' => '156', 'difficulty' => Difficulty::MEDIUM, 'competency' => 'M3NS-IIIj-37.1'],
      ['question_text' => '1,764 ÷ 7 = ?', 'correct_answer' => '252', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIj-37.1'],
      ['question_text' => '2,736 ÷ 9 = ?', 'correct_answer' => '304', 'difficulty' => Difficulty::HARD, 'competency' => 'M3NS-IIIj-37.1'],
    ];

    $this->createQuestions($questions, 3, QuestionType::ADDITION, $teacher, 0, 10);
    $this->createQuestions($questions, 3, QuestionType::SUBTRACTION, $teacher, 10, 10);
    $this->createQuestions($questions, 3, QuestionType::MULTIPLICATION, $teacher, 20, 10);
    $this->createQuestions($questions, 3, QuestionType::DIVISION, $teacher, 30, 5);
  }

  /**
   * Helper method to create questions for a specific type
   */
  private function createQuestions(array $questions, int $gradeLevel, QuestionType $questionType, User $teacher, int $startIndex, int $count): void
  {
    for ($i = $startIndex; $i < $startIndex + $count && $i < count($questions); $i++) {
      $questionData = $questions[$i];
      Question::create([
        'question_text' => $questionData['question_text'],
        'question_type' => $questionType,
        'grade_level' => $gradeLevel,
        'difficulty' => $questionData['difficulty'],
        'correct_answer' => $questionData['correct_answer'],
        'options' => null, // All questions are open-ended for now
        'deped_competency' => $this->depedCompetencies[$gradeLevel][$questionType->value][$questionData['competency']] ?? "Basic Math Operations - Grade {$gradeLevel}",
        'created_by' => $teacher->id,
      ]);
    }
  }
}
