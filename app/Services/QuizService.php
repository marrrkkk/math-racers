<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\QuizAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuizService
{
  /**
   * Create a new quiz session for a student.
   */
  public function createQuizSession(
    User $student,
    QuestionType $questionType,
    int $gradeLevel,
    int $totalQuestions = 10
  ): QuizSession {
    // Validate that user is a student
    if (!$student->isStudent()) {
      throw new \InvalidArgumentException('Only students can take quizzes');
    }

    // Validate grade level matches student's grade
    if ($student->grade_level !== $gradeLevel) {
      throw new \InvalidArgumentException('Grade level must match student\'s grade');
    }

    return QuizSession::create([
      'student_id' => $student->id,
      'question_type' => $questionType,
      'grade_level' => $gradeLevel,
      'total_questions' => $totalQuestions,
      'correct_answers' => 0,
      'points_earned' => 0,
      'time_taken' => 0,
    ]);
  }

  /**
   * Get questions for a quiz session.
   */
  public function getQuestionsForQuiz(
    QuestionType $questionType,
    int $gradeLevel,
    int $count = 10
  ): Collection {
    $questions = Question::ofType($questionType)
      ->forGrade($gradeLevel)
      ->inRandomOrder()
      ->limit($count)
      ->get();

    if ($questions->count() < $count) {
      throw new \Exception("Not enough questions available for {$questionType->label()} Grade {$gradeLevel}");
    }

    return $questions;
  }

  /**
   * Submit an answer for a quiz question.
   */
  public function submitAnswer(
    QuizSession $quizSession,
    Question $question,
    string $studentAnswer,
    int $timeTaken
  ): QuizAnswer {
    // Check if answer is correct
    $isCorrect = $question->isCorrectAnswer($studentAnswer);

    // Create quiz answer record
    $quizAnswer = QuizAnswer::create([
      'quiz_session_id' => $quizSession->id,
      'question_id' => $question->id,
      'student_answer' => trim($studentAnswer),
      'is_correct' => $isCorrect,
      'time_taken' => $timeTaken,
    ]);

    // Update quiz session statistics
    if ($isCorrect) {
      $quizSession->increment('correct_answers');
    }

    return $quizAnswer;
  }

  /**
   * Complete a quiz session and calculate final score.
   */
  public function completeQuizSession(QuizSession $quizSession, int $totalTimeTaken): QuizSession
  {
    DB::transaction(function () use ($quizSession, $totalTimeTaken) {
      // Update quiz session with completion data
      $quizSession->update([
        'time_taken' => $totalTimeTaken,
        'completed_at' => now(),
      ]);

      // Calculate and update final score
      $quizSession->points_earned = $quizSession->calculateScore();
      $quizSession->save();

      // Update student progress
      $this->updateStudentProgress($quizSession);
    });

    return $quizSession->fresh();
  }

  /**
   * Update student progress after quiz completion.
   */
  protected function updateStudentProgress(QuizSession $quizSession): void
  {
    $progressService = new ProgressService();
    $progressService->updateProgressFromQuizSession($quizSession);
  }

  /**
   * Get quiz session with answers.
   */
  public function getQuizSessionWithAnswers(int $sessionId): QuizSession
  {
    return QuizSession::with(['answers.question', 'student'])
      ->findOrFail($sessionId);
  }

  /**
   * Get student's quiz history for a specific topic and grade.
   */
  public function getStudentQuizHistory(
    User $student,
    QuestionType $questionType,
    int $gradeLevel,
    int $limit = 10
  ): Collection {
    return QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->orderBy('completed_at', 'desc')
      ->limit($limit)
      ->get();
  }

  /**
   * Get student's best performance for a topic.
   */
  public function getStudentBestPerformance(
    User $student,
    QuestionType $questionType,
    int $gradeLevel
  ): ?QuizSession {
    return QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->orderBy('points_earned', 'desc')
      ->orderBy('accuracy', 'desc')
      ->first();
  }

  /**
   * Get average performance statistics for a student.
   */
  public function getStudentAverageStats(
    User $student,
    QuestionType $questionType,
    int $gradeLevel
  ): array {
    $sessions = QuizSession::where('student_id', $student->id)
      ->ofType($questionType)
      ->forGrade($gradeLevel)
      ->completed()
      ->get();

    if ($sessions->isEmpty()) {
      return [
        'average_accuracy' => 0,
        'average_points' => 0,
        'average_time' => 0,
        'total_sessions' => 0,
      ];
    }

    return [
      'average_accuracy' => round($sessions->avg('accuracy'), 2),
      'average_points' => round($sessions->avg('points_earned'), 2),
      'average_time' => round($sessions->avg('time_taken'), 2),
      'total_sessions' => $sessions->count(),
    ];
  }

  /**
   * Check if a quiz session is valid and can be continued.
   */
  public function isValidSession(QuizSession $quizSession): bool
  {
    // Check if session is not completed
    if ($quizSession->isCompleted()) {
      return false;
    }

    // Check if session is not too old (e.g., 1 hour)
    $maxSessionAge = now()->subHour();
    if ($quizSession->created_at < $maxSessionAge) {
      return false;
    }

    return true;
  }

  /**
   * Abandon an incomplete quiz session.
   */
  public function abandonQuizSession(QuizSession $quizSession): void
  {
    if (!$quizSession->isCompleted()) {
      $quizSession->update([
        'completed_at' => now(),
        'points_earned' => 0,
      ]);
    }
  }
}
