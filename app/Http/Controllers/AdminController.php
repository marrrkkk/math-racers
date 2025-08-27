<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizSession;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminController extends Controller
{
  /**
   * Display the admin dashboard with system overview.
   */
  public function dashboard()
  {
    $user = auth()->user();

    // Get system statistics
    $totalUsers = User::count();
    $totalStudents = User::where('role', UserRole::STUDENT)->count();
    $totalTeachers = User::where('role', UserRole::TEACHER)->count();
    $totalQuestions = Question::count();
    $totalQuizSessions = QuizSession::completed()->count();

    // Get recent activity
    $recentUsers = User::latest()->limit(5)->get();
    $recentQuizSessions = QuizSession::completed()
      ->with('student:id,name')
      ->latest('completed_at')
      ->limit(10)
      ->get();

    // Get question distribution by grade and type
    $questionStats = Question::select('grade_level', 'question_type', DB::raw('count(*) as count'))
      ->groupBy('grade_level', 'question_type')
      ->get()
      ->groupBy('grade_level');

    // Get user activity by role
    $usersByRole = User::select('role', DB::raw('count(*) as count'))
      ->groupBy('role')
      ->get()
      ->pluck('count', 'role');

    return Inertia::render('Admin/Dashboard', [
      'user' => $user,
      'statistics' => [
        'total_users' => $totalUsers,
        'total_students' => $totalStudents,
        'total_teachers' => $totalTeachers,
        'total_questions' => $totalQuestions,
        'total_quiz_sessions' => $totalQuizSessions,
      ],
      'recentUsers' => $recentUsers,
      'recentQuizSessions' => $recentQuizSessions,
      'questionStats' => $questionStats,
      'usersByRole' => $usersByRole,
    ]);
  }

  /**
   * Display user management interface.
   */
  public function userManagement(Request $request)
  {
    $search = $request->get('search');
    $roleFilter = $request->get('role');
    $statusFilter = $request->get('status', 'all');

    $query = User::query();

    // Apply search filter
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // Apply role filter
    if ($roleFilter && $roleFilter !== 'all') {
      $query->where('role', $roleFilter);
    }

    // Apply status filter (active/inactive based on email verification)
    if ($statusFilter === 'active') {
      $query->whereNotNull('email_verified_at');
    } elseif ($statusFilter === 'inactive') {
      $query->whereNull('email_verified_at');
    }

    $users = $query->orderBy('created_at', 'desc')
      ->paginate(20)
      ->withQueryString();

    return Inertia::render('Admin/UserManagement', [
      'users' => $users,
      'filters' => [
        'search' => $search,
        'role' => $roleFilter,
        'status' => $statusFilter,
      ],
      'roles' => [
        'all' => 'All Roles',
        'student' => 'Students',
        'teacher' => 'Teachers',
        'admin' => 'Admins',
      ],
    ]);
  }

  /**
   * Update user status (activate/deactivate).
   */
  public function updateUserStatus(Request $request, User $user)
  {
    $request->validate([
      'status' => 'required|in:active,inactive',
    ]);

    if ($request->status === 'active') {
      $user->email_verified_at = now();
    } else {
      $user->email_verified_at = null;
    }

    $user->save();

    return back()->with('success', "User {$user->name} has been " . ($request->status === 'active' ? 'activated' : 'deactivated') . '.');
  }

  /**
   * Update user role.
   */
  public function updateUserRole(Request $request, User $user)
  {
    $request->validate([
      'role' => ['required', Rule::in(['student', 'teacher', 'admin'])],
    ]);

    // Prevent admin from changing their own role
    if ($user->id === auth()->id()) {
      return back()->withErrors(['role' => 'You cannot change your own role.']);
    }

    $user->role = UserRole::from($request->role);
    $user->save();

    return back()->with('success', "User {$user->name}'s role has been updated to {$request->role}.");
  }

  /**
   * Delete a user.
   */
  public function deleteUser(User $user)
  {
    // Prevent admin from deleting themselves
    if ($user->id === auth()->id()) {
      return back()->withErrors(['delete' => 'You cannot delete your own account.']);
    }

    // Delete related data first
    $user->quizSessions()->delete();
    $user->progress()->delete();
    $user->createdQuestions()->delete();

    $userName = $user->name;
    $user->delete();

    return back()->with('success', "User {$userName} has been deleted successfully.");
  }
  /**
   * Display system activity logs and basic reporting.
   */
  public function systemLogs(Request $request)
  {
    $dateFilter = $request->get('date_filter', 'last_7_days');
    $activityType = $request->get('activity_type', 'all');

    // Calculate date range based on filter
    $dateRange = $this->getDateRange($dateFilter);

    // Get user login activity (based on quiz sessions as proxy for activity)
    $userActivity = QuizSession::whereBetween('created_at', $dateRange)
      ->with('student:id,name,email')
      ->select('student_id', DB::raw('count(*) as quiz_count'), DB::raw('max(created_at) as last_activity'))
      ->groupBy('student_id')
      ->orderBy('last_activity', 'desc')
      ->limit(50)
      ->get();

    // Get quiz attempt statistics
    $quizStats = QuizSession::whereBetween('created_at', $dateRange)
      ->select(
        'question_type',
        'grade_level',
        DB::raw('count(*) as total_attempts'),
        DB::raw('avg(correct_answers) as avg_correct'),
        DB::raw('avg(time_taken) as avg_time')
      )
      ->groupBy('question_type', 'grade_level')
      ->get();

    // Get daily activity counts
    $dailyActivity = QuizSession::whereBetween('created_at', $dateRange)
      ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
      ->groupBy(DB::raw('DATE(created_at)'))
      ->orderBy('date')
      ->get();

    // Get system usage statistics
    $systemStats = [
      'total_active_users' => User::whereNotNull('email_verified_at')->count(),
      'new_users_period' => User::whereBetween('created_at', $dateRange)->count(),
      'total_quiz_attempts' => QuizSession::whereBetween('created_at', $dateRange)->count(),
      'completed_quizzes' => QuizSession::completed()->whereBetween('completed_at', $dateRange)->count(),
      'questions_created' => Question::whereBetween('created_at', $dateRange)->count(),
    ];

    return Inertia::render('Admin/SystemLogs', [
      'userActivity' => $userActivity,
      'quizStats' => $quizStats,
      'dailyActivity' => $dailyActivity,
      'systemStats' => $systemStats,
      'filters' => [
        'date_filter' => $dateFilter,
        'activity_type' => $activityType,
      ],
      'dateFilterOptions' => [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'last_7_days' => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
      ],
    ]);
  }

  /**
   * Display question bank management with bulk operations.
   */
  public function questionBankManagement(Request $request)
  {
    $search = $request->get('search');
    $gradeFilter = $request->get('grade');
    $typeFilter = $request->get('type');
    $creatorFilter = $request->get('creator');

    $query = Question::with('creator:id,name');

    // Apply search filter
    if ($search) {
      $query->where('question_text', 'like', "%{$search}%")
        ->orWhere('deped_competency', 'like', "%{$search}%");
    }

    // Apply grade filter
    if ($gradeFilter && $gradeFilter !== 'all') {
      $query->where('grade_level', $gradeFilter);
    }

    // Apply type filter
    if ($typeFilter && $typeFilter !== 'all') {
      $query->where('question_type', $typeFilter);
    }

    // Apply creator filter
    if ($creatorFilter && $creatorFilter !== 'all') {
      $query->where('created_by', $creatorFilter);
    }

    $questions = $query->orderBy('created_at', 'desc')
      ->paginate(20)
      ->withQueryString();

    // Get creators for filter dropdown
    $creators = User::whereIn('role', [UserRole::TEACHER, UserRole::ADMIN])
      ->select('id', 'name')
      ->orderBy('name')
      ->get();

    // Get question statistics
    $questionStats = [
      'total_questions' => Question::count(),
      'by_grade' => Question::select('grade_level', DB::raw('count(*) as count'))
        ->groupBy('grade_level')
        ->pluck('count', 'grade_level'),
      'by_type' => Question::select('question_type', DB::raw('count(*) as count'))
        ->groupBy('question_type')
        ->pluck('count', 'question_type'),
      'by_difficulty' => Question::select('difficulty', DB::raw('count(*) as count'))
        ->groupBy('difficulty')
        ->pluck('count', 'difficulty'),
    ];

    return Inertia::render('Admin/QuestionBankManagement', [
      'questions' => $questions,
      'creators' => $creators,
      'questionStats' => $questionStats,
      'filters' => [
        'search' => $search,
        'grade' => $gradeFilter,
        'type' => $typeFilter,
        'creator' => $creatorFilter,
      ],
    ]);
  }

  /**
   * Bulk delete questions.
   */
  public function bulkDeleteQuestions(Request $request)
  {
    $request->validate([
      'question_ids' => 'required|array',
      'question_ids.*' => 'exists:questions,id',
    ]);

    $deletedCount = Question::whereIn('id', $request->question_ids)->delete();

    return back()->with('success', "{$deletedCount} questions have been deleted successfully.");
  }

  /**
   * Export questions to CSV.
   */
  public function exportQuestions(Request $request)
  {
    $questions = Question::with('creator:id,name')->get();

    $csvData = [];
    $csvData[] = [
      'ID',
      'Question Text',
      'Type',
      'Grade Level',
      'Difficulty',
      'Correct Answer',
      'Options',
      'DepEd Competency',
      'Creator',
      'Created At'
    ];

    foreach ($questions as $question) {
      $csvData[] = [
        $question->id,
        $question->question_text,
        $question->question_type->value,
        $question->grade_level,
        $question->difficulty->value,
        $question->correct_answer,
        $question->options ? implode('|', $question->options) : '',
        $question->deped_competency,
        $question->creator->name ?? 'Unknown',
        $question->created_at->format('Y-m-d H:i:s'),
      ];
    }

    $filename = 'questions_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

    return response()->streamDownload(function () use ($csvData) {
      $handle = fopen('php://output', 'w');
      foreach ($csvData as $row) {
        fputcsv($handle, $row);
      }
      fclose($handle);
    }, $filename, [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename={$filename}",
    ]);
  }

  /**
   * Get date range based on filter.
   */
  private function getDateRange(string $filter): array
  {
    return match ($filter) {
      'today' => [now()->startOfDay(), now()->endOfDay()],
      'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
      'last_7_days' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
      'last_30_days' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
      'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
      'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
      default => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
    };
  }
}
