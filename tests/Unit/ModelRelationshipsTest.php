<?php

use App\Models\User;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\QuizAnswer;
use App\Models\StudentProgress;
use App\Enums\QuestionType;
use App\Enums\Difficulty;
use App\UserRole;

describe('Model Relationships', function () {
  beforeEach(function () {
    $this->artisan('migrate:fresh');
  });

  it('can create a user with proper role casting', function () {
    $user = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    expect($user->role)->toBe(UserRole::STUDENT);
    expect($user->grade_level)->toBe(2);
    expect($user->isStudent())->toBeTrue();
    expect($user->isTeacher())->toBeFalse();
    expect($user->isAdmin())->toBeFalse();
  });

  it('can create a question with proper relationships', function () {
    $teacher = User::factory()->create(['role' => UserRole::TEACHER]);

    $question = Question::create([
      'question_text' => '2 + 3 = ?',
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 1,
      'difficulty' => Difficulty::EASY,
      'correct_answer' => '5',
      'deped_competency' => 'Add whole numbers up to 10',
      'created_by' => $teacher->id,
    ]);

    expect($question->question_type)->toBe(QuestionType::ADDITION);
    expect($question->difficulty)->toBe(Difficulty::EASY);
    expect($question->creator->id)->toBe($teacher->id);
    expect($question->isCorrectAnswer('5'))->toBeTrue();
    expect($question->isCorrectAnswer('4'))->toBeFalse();
    expect($question->points)->toBe(5); // Easy difficulty = 5 points
  });

  it('can create a quiz session with proper relationships', function () {
    $student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $session = QuizSession::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 2,
      'total_questions' => 5,
      'correct_answers' => 4,
      'time_taken' => 120,
    ]);

    expect($session->student->id)->toBe($student->id);
    expect($session->question_type)->toBe(QuestionType::ADDITION);
    expect($session->accuracy)->toBe(80.0);
    expect($session->performance_rating)->toBe('Very Good');
    expect($session->isCompleted())->toBeFalse();

    $session->complete();
    expect($session->isCompleted())->toBeTrue();
    expect($session->points_earned)->toBeGreaterThan(0);
  });

  it('can create quiz answers with proper relationships', function () {
    $student = User::factory()->create(['role' => UserRole::STUDENT]);
    $teacher = User::factory()->create(['role' => UserRole::TEACHER]);

    $question = Question::create([
      'question_text' => '5 - 2 = ?',
      'question_type' => QuestionType::SUBTRACTION,
      'grade_level' => 1,
      'difficulty' => Difficulty::EASY,
      'correct_answer' => '3',
      'deped_competency' => 'Subtract whole numbers up to 10',
      'created_by' => $teacher->id,
    ]);

    $session = QuizSession::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::SUBTRACTION,
      'grade_level' => 1,
      'total_questions' => 1,
      'time_taken' => 30,
    ]);

    $answer = QuizAnswer::create([
      'quiz_session_id' => $session->id,
      'question_id' => $question->id,
      'student_answer' => '3',
      'is_correct' => true,
      'time_taken' => 8,
    ]);

    expect($answer->quizSession->id)->toBe($session->id);
    expect($answer->question->id)->toBe($question->id);
    expect($answer->is_correct)->toBeTrue();
    expect($answer->isQuickAnswer())->toBeTrue();
    expect($answer->response_time_category)->toBe('Fast');
    expect($answer->points_earned)->toBeGreaterThan(5); // Base points + time bonus
  });

  it('can create student progress with proper relationships', function () {
    $student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 2,
    ]);

    $progress = StudentProgress::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::MULTIPLICATION,
      'grade_level' => 2,
      'total_points' => 150,
      'mastery_level' => 75.50,
    ]);

    expect($progress->student->id)->toBe($student->id);
    expect($progress->question_type)->toBe(QuestionType::MULTIPLICATION);
    expect($progress->mastery_percentage)->toBe(75.50);
    expect($progress->mastery_category)->toBe('Proficient');
    expect($progress->hasMastery())->toBeFalse(); // Needs 80% for mastery

    // Test badge awarding
    $badgeAwarded = $progress->awardBadge('first_quiz', ['points' => 50]);
    expect($badgeAwarded)->toBeTrue();
    expect($progress->hasBadge('first_quiz'))->toBeTrue();
    expect($progress->total_badges)->toBe(1);

    // Test duplicate badge prevention
    $duplicateBadge = $progress->awardBadge('first_quiz', ['points' => 60]);
    expect($duplicateBadge)->toBeFalse();
    expect($progress->total_badges)->toBe(1);
  });

  it('can test user relationships with quiz data', function () {
    $student = User::factory()->create([
      'role' => UserRole::STUDENT,
      'grade_level' => 3,
    ]);

    // Create quiz sessions
    $session1 = QuizSession::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 3,
      'total_questions' => 5,
      'correct_answers' => 5,
      'points_earned' => 100,
      'time_taken' => 60,
      'completed_at' => now(),
    ]);

    $session2 = QuizSession::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::SUBTRACTION,
      'grade_level' => 3,
      'total_questions' => 3,
      'correct_answers' => 2,
      'points_earned' => 50,
      'time_taken' => 45,
      'completed_at' => now(),
    ]);

    // Create progress records
    $progress1 = StudentProgress::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::ADDITION,
      'grade_level' => 3,
      'total_points' => 100,
      'mastery_level' => 85.0,
    ]);

    $progress2 = StudentProgress::create([
      'student_id' => $student->id,
      'question_type' => QuestionType::SUBTRACTION,
      'grade_level' => 3,
      'total_points' => 50,
      'mastery_level' => 70.0,
    ]);

    // Test user relationships
    expect($student->quizSessions)->toHaveCount(2);
    expect($student->completedQuizSessions)->toHaveCount(2);
    expect($student->progress)->toHaveCount(2);
    expect($student->total_points)->toBe(150);

    // Test specific progress retrieval
    $additionProgress = $student->getProgressFor('addition', 3);
    expect($additionProgress)->not->toBeNull();
    expect($additionProgress->mastery_level)->toBe(85.0);
  });

  it('validates model data correctly', function () {
    // Test Question validation rules
    $questionRules = Question::validationRules();
    expect($questionRules)->toHaveKey('question_text');
    expect($questionRules)->toHaveKey('question_type');
    expect($questionRules)->toHaveKey('grade_level');
    expect($questionRules)->toHaveKey('difficulty');
    expect($questionRules)->toHaveKey('correct_answer');
    expect($questionRules)->toHaveKey('deped_competency');

    // Test QuizSession validation rules
    $sessionRules = QuizSession::validationRules();
    expect($sessionRules)->toHaveKey('student_id');
    expect($sessionRules)->toHaveKey('question_type');
    expect($sessionRules)->toHaveKey('grade_level');
    expect($sessionRules)->toHaveKey('total_questions');

    // Test QuizAnswer validation rules
    $answerRules = QuizAnswer::validationRules();
    expect($answerRules)->toHaveKey('quiz_session_id');
    expect($answerRules)->toHaveKey('question_id');
    expect($answerRules)->toHaveKey('student_answer');
    expect($answerRules)->toHaveKey('is_correct');
    expect($answerRules)->toHaveKey('time_taken');

    // Test StudentProgress validation rules
    $progressRules = StudentProgress::validationRules();
    expect($progressRules)->toHaveKey('student_id');
    expect($progressRules)->toHaveKey('question_type');
    expect($progressRules)->toHaveKey('grade_level');
  });
});
