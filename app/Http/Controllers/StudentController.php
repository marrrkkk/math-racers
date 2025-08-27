<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\StudentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentController extends Controller
{
  public function dashboard()
  {
    $user = Auth::user();

    return Inertia::render('Student/Dashboard', [
      'user' => $user,
    ]);
  }

  public function selectGrade(Request $request)
  {
    $request->validate([
      'grade_level' => 'required|integer|min:1|max:3'
    ]);

    $user = Auth::user();
    $user->update(['grade_level' => $request->grade_level]);

    return redirect()->route('student.topics', ['grade' => $request->grade_level]);
  }

  public function topics($grade)
  {
    if (!in_array($grade, [1, 2, 3])) {
      abort(404);
    }

    $user = Auth::user();

    // Define topics available for each grade level
    $topics = [
      1 => ['addition', 'subtraction'],
      2 => ['addition', 'subtraction', 'multiplication'],
      3 => ['addition', 'subtraction', 'multiplication', 'division']
    ];

    return Inertia::render('Student/Topics', [
      'user' => $user,
      'grade' => (int) $grade,
      'topics' => $topics[$grade] ?? []
    ]);
  }

  public function startQuiz(Request $request)
  {
    $request->validate([
      'grade' => 'required|integer|min:1|max:3',
      'topic' => 'required|in:addition,subtraction,multiplication,division'
    ]);

    $user = Auth::user();
    $grade = $request->grade;
    $topic = $request->topic;

    // Get questions for this grade and topic
    $questions = Question::forGrade($grade)
      ->ofType(QuestionType::from($topic))
      ->inRandomOrder()
      ->limit(10)
      ->get();

    if ($questions->isEmpty()) {
      return back()->with('error', 'No questions available for this topic and grade level.');
    }

    // Create quiz session with question IDs
    $session = QuizSession::create([
      'student_id' => $user->id,
      'question_type' => $topic,
      'grade_level' => $grade,
      'total_questions' => $questions->count(),
      'question_ids' => $questions->pluck('id')->toArray(),
      'correct_answers' => 0,
      'points_earned' => 0,
      'time_taken' => 0,
    ]);

    return redirect()->route('student.quiz', ['session' => $session->id]);
  }

  public function quiz($session)
  {
    $user = Auth::user();
    $quizSession = QuizSession::where('id', $session)
      ->where('student_id', $user->id)
      ->whereNull('completed_at')
      ->firstOrFail();

    // Get questions for this session (in the correct order)
    $questions = $quizSession->questions;

    return Inertia::render('Student/QuizGame', [
      'user' => $user,
      'grade' => $quizSession->grade_level,
      'topic' => $quizSession->question_type->value,
      'questions' => $questions,
      'sessionId' => $quizSession->id,
      'totalQuestions' => $quizSession->total_questions
    ]);
  }

  public function submitAnswer(Request $request, $session)
  {
    $request->validate([
      'question_id' => 'required|exists:questions,id',
      'answer' => 'required|string',
      'time_taken' => 'required|integer|min:0'
    ]);

    $user = Auth::user();
    $quizSession = QuizSession::where('id', $session)
      ->where('student_id', $user->id)
      ->whereNull('completed_at')
      ->firstOrFail();

    $question = Question::findOrFail($request->question_id);
    $isCorrect = $question->isCorrectAnswer($request->answer);

    // Save the answer
    QuizAnswer::create([
      'quiz_session_id' => $quizSession->id,
      'question_id' => $question->id,
      'student_answer' => $request->answer,
      'is_correct' => $isCorrect,
      'time_taken' => $request->time_taken
    ]);

    // Update session if correct
    if ($isCorrect) {
      $quizSession->increment('correct_answers');
    }

    // Return success response
    return back()->with('success', 'Answer submitted successfully');
  }

  public function completeQuiz(Request $request, $session)
  {
    $request->validate([
      'total_time' => 'required|integer|min:0'
    ]);

    $user = Auth::user();
    $quizSession = QuizSession::where('id', $session)
      ->where('student_id', $user->id)
      ->whereNull('completed_at')
      ->firstOrFail();

    // Update session with completion data
    $quizSession->update([
      'time_taken' => $request->total_time,
      'completed_at' => now()
    ]);

    // Calculate final score
    $quizSession->points_earned = $quizSession->calculateScore();
    $quizSession->save();

    return Inertia::render('Student/QuizGame', [
      'user' => $user,
      'grade' => $quizSession->grade_level,
      'topic' => $quizSession->question_type->value,
      'questions' => [],
      'sessionId' => $quizSession->id,
      'totalQuestions' => $quizSession->total_questions,
      'finalScore' => [
        'points' => $quizSession->points_earned,
        'accuracy' => $quizSession->accuracy,
        'correct_answers' => $quizSession->correct_answers,
        'total_questions' => $quizSession->total_questions,
        'time_taken' => $quizSession->time_taken
      ]
    ]);
  }

  public function progress()
  {
    $user = Auth::user();

    if (!$user->isStudent() || !$user->grade_level) {
      return redirect()->route('student.dashboard');
    }

    // Get student's progress for their grade level
    $progressData = $user->progress()
      ->where('grade_level', $user->grade_level)
      ->get()
      ->keyBy('question_type');

    // Get recent quiz sessions
    $recentSessions = $user->completedQuizSessions()
      ->where('grade_level', $user->grade_level)
      ->orderBy('completed_at', 'desc')
      ->limit(10)
      ->get();

    // Calculate overall statistics
    $totalPoints = $user->total_points;
    $totalBadges = $user->all_badges;
    $totalQuizzes = $user->completedQuizSessions()->where('grade_level', $user->grade_level)->count();

    // Calculate average accuracy
    $averageAccuracy = $user->completedQuizSessions()
      ->where('grade_level', $user->grade_level)
      ->avg('accuracy') ?? 0;

    // Get topic-specific statistics
    $topicStats = [];
    $topics = ['addition', 'subtraction', 'multiplication', 'division'];

    foreach ($topics as $topic) {
      $progress = $progressData->get($topic);
      $sessions = $user->completedQuizSessions()
        ->where('grade_level', $user->grade_level)
        ->where('question_type', $topic)
        ->get();

      $topicStats[$topic] = [
        'total_points' => $progress?->total_points ?? 0,
        'mastery_level' => $progress?->mastery_percentage ?? 0,
        'mastery_category' => $progress?->mastery_category ?? 'Needs Support',
        'badges_earned' => $progress?->badges_earned ?? [],
        'total_quizzes' => $sessions->count(),
        'average_accuracy' => $sessions->avg('accuracy') ?? 0,
        'best_score' => $sessions->max('points_earned') ?? 0,
        'last_activity' => $progress?->activity_status ?? 'Never Active'
      ];
    }

    return Inertia::render('Student/Progress', [
      'user' => $user,
      'progressData' => $progressData->values(),
      'recentSessions' => $recentSessions,
      'statistics' => [
        'total_points' => $totalPoints,
        'total_badges' => count($totalBadges),
        'total_quizzes' => $totalQuizzes,
        'average_accuracy' => round($averageAccuracy, 1)
      ],
      'topicStats' => $topicStats,
      'allBadges' => $totalBadges
    ]);
  }

  public function leaderboard(Request $request)
  {
    $user = Auth::user();

    if (!$user->isStudent() || !$user->grade_level) {
      return redirect()->route('student.dashboard');
    }

    $gradeFilter = $request->get('grade', $user->grade_level);
    $topicFilter = $request->get('topic', 'all');

    // Validate filters
    if (!in_array($gradeFilter, [1, 2, 3])) {
      $gradeFilter = $user->grade_level;
    }

    if (!in_array($topicFilter, ['all', 'addition', 'subtraction', 'multiplication', 'division'])) {
      $topicFilter = 'all';
    }

    // Get leaderboard data
    if ($topicFilter === 'all') {
      // Overall leaderboard based on total points for the grade
      $leaderboard = \App\Models\User::where('role', 'student')
        ->where('grade_level', $gradeFilter)
        ->with(['progress' => function ($query) use ($gradeFilter) {
          $query->where('grade_level', $gradeFilter);
        }])
        ->get()
        ->map(function ($student) {
          $totalPoints = $student->progress->sum('total_points');
          return [
            'student' => $student,
            'total_points' => $totalPoints
          ];
        })
        ->filter(function ($item) {
          return $item['total_points'] > 0;
        })
        ->sortByDesc('total_points')
        ->take(50)
        ->values()
        ->map(function ($item, $index) {
          return [
            'rank' => $index + 1,
            'name' => $item['student']->name,
            'points' => $item['total_points'],
            'is_current_user' => $item['student']->id === auth()->id(),
            'badge_count' => count($item['student']->all_badges)
          ];
        });
    } else {
      // Topic-specific leaderboard
      $leaderboard = \App\Models\StudentProgress::where('grade_level', $gradeFilter)
        ->where('question_type', $topicFilter)
        ->where('total_points', '>', 0)
        ->with('student:id,name')
        ->orderBy('total_points', 'desc')
        ->limit(50)
        ->get()
        ->map(function ($progress, $index) {
          return [
            'rank' => $index + 1,
            'name' => $progress->student->name,
            'points' => $progress->total_points,
            'mastery_level' => $progress->mastery_percentage,
            'is_current_user' => $progress->student_id === auth()->id(),
            'badge_count' => count($progress->badges_earned ?? [])
          ];
        });
    }

    // Find current user's position if not in top 50
    $currentUserRank = null;
    $currentUserInTop = $leaderboard->firstWhere('is_current_user', true);

    if (!$currentUserInTop) {
      if ($topicFilter === 'all') {
        $userTotalPoints = $user->progress()
          ->where('grade_level', $gradeFilter)
          ->sum('total_points');

        $currentUserRank = \App\Models\User::where('role', 'student')
          ->where('grade_level', $gradeFilter)
          ->with(['progress' => function ($query) use ($gradeFilter) {
            $query->where('grade_level', $gradeFilter);
          }])
          ->get()
          ->map(function ($student) {
            return $student->progress->sum('total_points');
          })
          ->filter(function ($points) use ($userTotalPoints) {
            return $points > $userTotalPoints;
          })
          ->count() + 1;
      } else {
        $userProgress = $user->getProgressFor($topicFilter, $gradeFilter);
        if ($userProgress) {
          $currentUserRank = $userProgress->rank;
        }
      }
    }

    return Inertia::render('Student/Leaderboard', [
      'user' => $user,
      'leaderboard' => $leaderboard,
      'currentUserRank' => $currentUserRank,
      'filters' => [
        'grade' => $gradeFilter,
        'topic' => $topicFilter
      ],
      'availableGrades' => [1, 2, 3],
      'availableTopics' => [
        'all' => 'Overall',
        'addition' => 'Addition',
        'subtraction' => 'Subtraction',
        'multiplication' => 'Multiplication',
        'division' => 'Division'
      ]
    ]);
  }
}
