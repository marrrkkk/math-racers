<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizSession;
use App\Models\StudentProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeacherController extends Controller
{
  /**
   * Display the teacher dashboard with student performance overview.
   */
  public function dashboard()
  {
    $teacher = Auth::user();

    // Get all students
    $students = User::where('role', 'student')
      ->with(['progress', 'completedQuizSessions'])
      ->get();

    // Calculate overview statistics
    $totalStudents = $students->count();
    $activeStudents = $students->filter(function ($student) {
      return $student->completedQuizSessions->where('completed_at', '>=', now()->subDays(7))->count() > 0;
    })->count();

    // Get recent quiz activity (last 7 days)
    $recentQuizzes = QuizSession::completed()
      ->where('completed_at', '>=', now()->subDays(7))
      ->with('student:id,name,grade_level')
      ->orderBy('completed_at', 'desc')
      ->limit(10)
      ->get();

    // Calculate grade-level statistics
    $gradeStats = [];
    for ($grade = 1; $grade <= 3; $grade++) {
      $gradeStudents = $students->where('grade_level', $grade);
      $gradeQuizzes = QuizSession::completed()
        ->where('grade_level', $grade)
        ->where('completed_at', '>=', now()->subDays(30))
        ->get();

      $gradeStats[$grade] = [
        'total_students' => $gradeStudents->count(),
        'active_students' => $gradeStudents->filter(function ($student) {
          return $student->completedQuizSessions->where('completed_at', '>=', now()->subDays(7))->count() > 0;
        })->count(),
        'total_quizzes' => $gradeQuizzes->count(),
        'average_accuracy' => $gradeQuizzes->avg('accuracy') ?? 0,
        'average_points' => $gradeQuizzes->avg('points_earned') ?? 0,
      ];
    }

    // Get topic performance overview
    $topicPerformance = [];
    $topics = ['addition', 'subtraction', 'multiplication', 'division'];

    foreach ($topics as $topic) {
      $topicQuizzes = QuizSession::completed()
        ->where('question_type', $topic)
        ->where('completed_at', '>=', now()->subDays(30))
        ->get();

      $topicPerformance[$topic] = [
        'total_attempts' => $topicQuizzes->count(),
        'average_accuracy' => $topicQuizzes->avg('accuracy') ?? 0,
        'students_attempted' => $topicQuizzes->pluck('student_id')->unique()->count(),
        'mastery_rate' => StudentProgress::where('question_type', $topic)
          ->where('mastery_level', '>=', 80)
          ->count(),
      ];
    }

    return Inertia::render('Teacher/Dashboard', [
      'teacher' => $teacher,
      'overview' => [
        'total_students' => $totalStudents,
        'active_students' => $activeStudents,
        'total_questions' => Question::count(),
        'recent_quizzes_count' => $recentQuizzes->count(),
      ],
      'recentQuizzes' => $recentQuizzes,
      'gradeStats' => $gradeStats,
      'topicPerformance' => $topicPerformance,
    ]);
  }

  /**
   * Display student performance analytics.
   */
  public function studentPerformance(Request $request)
  {
    $gradeFilter = $request->get('grade', 'all');
    $topicFilter = $request->get('topic', 'all');
    $search = $request->get('search', '');

    $query = User::where('role', 'student')
      ->with(['progress', 'completedQuizSessions']);

    // Apply filters
    if ($gradeFilter !== 'all') {
      $query->where('grade_level', $gradeFilter);
    }

    if ($search) {
      $query->where('name', 'like', '%' . $search . '%');
    }

    $students = $query->get();

    // Process student data with performance metrics
    $studentData = $students->map(function ($student) use ($topicFilter) {
      $quizSessions = $student->completedQuizSessions;

      if ($topicFilter !== 'all') {
        $quizSessions = $quizSessions->where('question_type', $topicFilter);
      }

      $progress = $student->progress;
      if ($topicFilter !== 'all') {
        $progress = $progress->where('question_type', $topicFilter);
      }

      return [
        'id' => $student->id,
        'name' => $student->name,
        'grade_level' => $student->grade_level,
        'total_points' => $progress->sum('total_points'),
        'total_quizzes' => $quizSessions->count(),
        'average_accuracy' => $quizSessions->avg('accuracy') ?? 0,
        'best_score' => $quizSessions->max('points_earned') ?? 0,
        'last_activity' => $quizSessions->max('completed_at'),
        'mastery_topics' => $progress->where('mastery_level', '>=', 80)->count(),
        'total_badges' => $progress->sum(function ($p) {
          return count($p->badges_earned ?? []);
        }),
      ];
    });

    return Inertia::render('Teacher/StudentPerformance', [
      'students' => $studentData->sortByDesc('total_points')->values(),
      'filters' => [
        'grade' => $gradeFilter,
        'topic' => $topicFilter,
        'search' => $search,
      ],
      'availableGrades' => [
        'all' => 'All Grades',
        '1' => 'Grade 1',
        '2' => 'Grade 2',
        '3' => 'Grade 3',
      ],
      'availableTopics' => [
        'all' => 'All Topics',
        'addition' => 'Addition',
        'subtraction' => 'Subtraction',
        'multiplication' => 'Multiplication',
        'division' => 'Division',
      ],
    ]);
  }

  /**
   * Display detailed analytics for a specific student.
   */
  public function studentDetail($studentId)
  {
    $student = User::where('role', 'student')
      ->with(['progress', 'completedQuizSessions.answers.question'])
      ->findOrFail($studentId);

    // Get student's quiz sessions grouped by topic
    $quizzesByTopic = $student->completedQuizSessions
      ->groupBy('question_type')
      ->map(function ($quizzes) {
        return [
          'total_attempts' => $quizzes->count(),
          'average_accuracy' => $quizzes->avg('accuracy'),
          'best_score' => $quizzes->max('points_earned'),
          'total_points' => $quizzes->sum('points_earned'),
          'recent_sessions' => $quizzes->sortByDesc('completed_at')->take(5)->values(),
        ];
      });

    // Get progress data by topic
    $progressByTopic = $student->progress
      ->keyBy('question_type')
      ->map(function ($progress) {
        return [
          'total_points' => $progress->total_points,
          'mastery_level' => $progress->mastery_percentage,
          'mastery_category' => $progress->mastery_category,
          'badges_earned' => $progress->badges_earned ?? [],
          'last_activity' => $progress->last_activity,
        ];
      });

    // Calculate competency analysis
    $competencyAnalysis = $this->analyzeStudentCompetencies($student);

    // Get recent activity timeline
    $activityTimeline = $student->completedQuizSessions
      ->sortByDesc('completed_at')
      ->take(20)
      ->map(function ($session) {
        return [
          'date' => $session->completed_at,
          'topic' => $session->question_type,
          'grade' => $session->grade_level,
          'score' => $session->points_earned,
          'accuracy' => $session->accuracy,
          'questions' => $session->total_questions,
          'time_taken' => $session->time_taken,
        ];
      });

    return Inertia::render('Teacher/StudentDetail', [
      'student' => [
        'id' => $student->id,
        'name' => $student->name,
        'grade_level' => $student->grade_level,
        'total_points' => $student->total_points,
        'total_badges' => count($student->all_badges),
      ],
      'quizzesByTopic' => $quizzesByTopic,
      'progressByTopic' => $progressByTopic,
      'competencyAnalysis' => $competencyAnalysis,
      'activityTimeline' => $activityTimeline,
    ]);
  }

  /**
   * Display class performance aggregation and competency tracking.
   */
  public function classPerformance(Request $request)
  {
    $gradeFilter = $request->get('grade', 1);
    $timeFilter = $request->get('time_period', '30'); // days

    // Validate grade filter
    if (!in_array($gradeFilter, [1, 2, 3])) {
      $gradeFilter = 1;
    }

    $startDate = now()->subDays((int) $timeFilter);

    // Get class overview statistics
    $classStats = $this->getClassStatistics($gradeFilter, $startDate);

    // Get competency mastery data
    $competencyMastery = $this->getCompetencyMastery($gradeFilter);

    // Get topic performance trends
    $topicTrends = $this->getTopicPerformanceTrends($gradeFilter, $startDate);

    // Get struggling students (accuracy < 60%)
    $strugglingStudents = $this->getStrugglingStudents($gradeFilter, $startDate);

    // Get top performers
    $topPerformers = $this->getTopPerformers($gradeFilter, $startDate);

    return Inertia::render('Teacher/ClassPerformance', [
      'classStats' => $classStats,
      'competencyMastery' => $competencyMastery,
      'topicTrends' => $topicTrends,
      'strugglingStudents' => $strugglingStudents,
      'topPerformers' => $topPerformers,
      'filters' => [
        'grade' => $gradeFilter,
        'time_period' => $timeFilter,
      ],
      'availableGrades' => [1, 2, 3],
      'availableTimePeriods' => [
        '7' => 'Last 7 days',
        '30' => 'Last 30 days',
        '90' => 'Last 3 months',
        '365' => 'Last year',
      ],
    ]);
  }

  /**
   * Display and manage topic assignments for students.
   */
  public function topicAssignments(Request $request)
  {
    $gradeFilter = $request->get('grade', 1);

    // Get students for the selected grade
    $students = User::where('role', 'student')
      ->where('grade_level', $gradeFilter)
      ->with(['progress'])
      ->orderBy('name')
      ->get();

    // Get available topics for the grade
    $availableTopics = $this->getAvailableTopicsForGrade($gradeFilter);

    // Get current assignments (we'll store these in a new table or use existing progress)
    $assignments = $this->getCurrentAssignments($gradeFilter);

    return Inertia::render('Teacher/TopicAssignments', [
      'students' => $students->map(function ($student) {
        return [
          'id' => $student->id,
          'name' => $student->name,
          'grade_level' => $student->grade_level,
          'progress' => $student->progress->keyBy('question_type'),
        ];
      }),
      'availableTopics' => $availableTopics,
      'assignments' => $assignments,
      'filters' => [
        'grade' => $gradeFilter,
      ],
      'availableGrades' => [1, 2, 3],
    ]);
  }

  /**
   * Assign topics to students.
   */
  public function assignTopics(Request $request)
  {
    $request->validate([
      'student_ids' => 'required|array',
      'student_ids.*' => 'exists:users,id',
      'topics' => 'required|array',
      'topics.*' => 'in:addition,subtraction,multiplication,division',
      'grade_level' => 'required|integer|between:1,3',
      'due_date' => 'nullable|date|after:today',
    ]);

    // For now, we'll create or update student progress records to indicate assignments
    // In a full implementation, you might want a separate assignments table
    foreach ($request->student_ids as $studentId) {
      foreach ($request->topics as $topic) {
        StudentProgress::updateOrCreate(
          [
            'student_id' => $studentId,
            'question_type' => $topic,
            'grade_level' => $request->grade_level,
          ],
          [
            'total_points' => 0,
            'badges_earned' => [],
            'mastery_level' => 0,
            'last_activity' => now(),
          ]
        );
      }
    }

    return back()->with('success', 'Topics assigned successfully!');
  }

  /**
   * Analyze student competencies based on quiz performance.
   */
  private function analyzeStudentCompetencies(User $student): array
  {
    $competencies = [];
    $topics = ['addition', 'subtraction', 'multiplication', 'division'];

    foreach ($topics as $topic) {
      $quizzes = $student->completedQuizSessions
        ->where('question_type', $topic)
        ->where('grade_level', $student->grade_level);

      if ($quizzes->isEmpty()) {
        continue;
      }

      $averageAccuracy = $quizzes->avg('accuracy');
      $totalAttempts = $quizzes->count();
      $recentPerformance = $quizzes->sortByDesc('completed_at')->take(5)->avg('accuracy');

      $competencies[$topic] = [
        'overall_accuracy' => round($averageAccuracy, 1),
        'recent_accuracy' => round($recentPerformance, 1),
        'total_attempts' => $totalAttempts,
        'improvement_trend' => $recentPerformance > $averageAccuracy ? 'improving' : ($recentPerformance < $averageAccuracy ? 'declining' : 'stable'),
        'mastery_level' => $this->calculateMasteryLevel($averageAccuracy),
        'recommendations' => $this->getRecommendations($topic, $averageAccuracy),
      ];
    }

    return $competencies;
  }

  /**
   * Get class statistics for a specific grade and time period.
   */
  private function getClassStatistics(int $grade, $startDate): array
  {
    $students = User::where('role', 'student')
      ->where('grade_level', $grade)
      ->get();

    $quizzes = QuizSession::completed()
      ->where('grade_level', $grade)
      ->where('completed_at', '>=', $startDate)
      ->get();

    return [
      'total_students' => $students->count(),
      'active_students' => $quizzes->pluck('student_id')->unique()->count(),
      'total_quizzes' => $quizzes->count(),
      'average_accuracy' => round($quizzes->avg('accuracy') ?? 0, 1),
      'average_score' => round($quizzes->avg('points_earned') ?? 0, 1),
      'completion_rate' => $students->count() > 0 ?
        round(($quizzes->pluck('student_id')->unique()->count() / $students->count()) * 100, 1) : 0,
    ];
  }

  /**
   * Get competency mastery data for a grade.
   */
  private function getCompetencyMastery(int $grade): array
  {
    $topics = ['addition', 'subtraction', 'multiplication', 'division'];
    $mastery = [];

    foreach ($topics as $topic) {
      $progress = StudentProgress::where('grade_level', $grade)
        ->where('question_type', $topic)
        ->get();

      $totalStudents = User::where('role', 'student')
        ->where('grade_level', $grade)
        ->count();

      $masteryLevels = [
        'expert' => $progress->where('mastery_level', '>=', 90)->count(),
        'advanced' => $progress->where('mastery_level', '>=', 80)->where('mastery_level', '<', 90)->count(),
        'proficient' => $progress->where('mastery_level', '>=', 70)->where('mastery_level', '<', 80)->count(),
        'developing' => $progress->where('mastery_level', '>=', 60)->where('mastery_level', '<', 70)->count(),
        'beginning' => $progress->where('mastery_level', '>=', 50)->where('mastery_level', '<', 60)->count(),
        'needs_support' => $progress->where('mastery_level', '<', 50)->count(),
      ];

      $mastery[$topic] = [
        'total_students' => $totalStudents,
        'students_attempted' => $progress->count(),
        'mastery_levels' => $masteryLevels,
        'average_mastery' => round($progress->avg('mastery_level') ?? 0, 1),
      ];
    }

    return $mastery;
  }

  /**
   * Get topic performance trends over time.
   */
  private function getTopicPerformanceTrends(int $grade, $startDate): array
  {
    $topics = ['addition', 'subtraction', 'multiplication', 'division'];
    $trends = [];

    foreach ($topics as $topic) {
      $dailyPerformance = QuizSession::completed()
        ->where('grade_level', $grade)
        ->where('question_type', $topic)
        ->where('completed_at', '>=', $startDate)
        ->selectRaw('DATE(completed_at) as date, AVG(accuracy) as avg_accuracy, COUNT(*) as quiz_count')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

      $trends[$topic] = $dailyPerformance->map(function ($item) {
        return [
          'date' => $item->date,
          'accuracy' => round($item->avg_accuracy, 1),
          'quiz_count' => $item->quiz_count,
        ];
      });
    }

    return $trends;
  }

  /**
   * Get students who are struggling (accuracy < 60%).
   */
  private function getStrugglingStudents(int $grade, $startDate): array
  {
    return User::where('role', 'student')
      ->where('grade_level', $grade)
      ->with(['completedQuizSessions' => function ($query) use ($startDate) {
        $query->where('completed_at', '>=', $startDate);
      }])
      ->get()
      ->filter(function ($student) {
        $avgAccuracy = $student->completedQuizSessions->avg('accuracy');
        return $avgAccuracy && $avgAccuracy < 60;
      })
      ->map(function ($student) {
        return [
          'id' => $student->id,
          'name' => $student->name,
          'average_accuracy' => round($student->completedQuizSessions->avg('accuracy'), 1),
          'quiz_count' => $student->completedQuizSessions->count(),
          'weakest_topic' => $this->getWeakestTopic($student),
        ];
      })
      ->values()
      ->toArray();
  }

  /**
   * Get top performing students.
   */
  private function getTopPerformers(int $grade, $startDate): array
  {
    return User::where('role', 'student')
      ->where('grade_level', $grade)
      ->with(['completedQuizSessions' => function ($query) use ($startDate) {
        $query->where('completed_at', '>=', $startDate);
      }])
      ->get()
      ->filter(function ($student) {
        return $student->completedQuizSessions->count() > 0;
      })
      ->sortByDesc(function ($student) {
        return $student->completedQuizSessions->avg('accuracy');
      })
      ->take(10)
      ->map(function ($student) {
        return [
          'id' => $student->id,
          'name' => $student->name,
          'average_accuracy' => round($student->completedQuizSessions->avg('accuracy'), 1),
          'total_points' => $student->completedQuizSessions->sum('points_earned'),
          'quiz_count' => $student->completedQuizSessions->count(),
          'strongest_topic' => $this->getStrongestTopic($student),
        ];
      })
      ->values()
      ->toArray();
  }

  /**
   * Get available topics for a specific grade.
   */
  private function getAvailableTopicsForGrade(int $grade): array
  {
    $allTopics = [
      'addition' => 'Addition',
      'subtraction' => 'Subtraction',
      'multiplication' => 'Multiplication',
      'division' => 'Division',
    ];

    // Grade 1 students typically start with addition and subtraction
    if ($grade === 1) {
      return array_intersect_key($allTopics, array_flip(['addition', 'subtraction']));
    }

    return $allTopics;
  }

  /**
   * Get current topic assignments for a grade.
   */
  private function getCurrentAssignments(int $grade): array
  {
    // This is a simplified implementation
    // In a full system, you'd have a dedicated assignments table
    return StudentProgress::where('grade_level', $grade)
      ->with('student:id,name')
      ->get()
      ->groupBy('student_id')
      ->map(function ($assignments) {
        return [
          'student' => $assignments->first()->student,
          'topics' => $assignments->pluck('question_type')->toArray(),
          'last_updated' => $assignments->max('updated_at'),
        ];
      })
      ->values()
      ->toArray();
  }

  /**
   * Calculate mastery level based on accuracy.
   */
  private function calculateMasteryLevel(float $accuracy): string
  {
    return match (true) {
      $accuracy >= 90 => 'Expert',
      $accuracy >= 80 => 'Advanced',
      $accuracy >= 70 => 'Proficient',
      $accuracy >= 60 => 'Developing',
      $accuracy >= 50 => 'Beginning',
      default => 'Needs Support'
    };
  }

  /**
   * Get recommendations based on topic and accuracy.
   */
  private function getRecommendations(string $topic, float $accuracy): array
  {
    if ($accuracy >= 80) {
      return ["Excellent work in {$topic}! Consider advancing to more challenging problems."];
    }

    if ($accuracy >= 60) {
      return [
        "Good progress in {$topic}. Focus on consistent practice.",
        "Review problem-solving strategies for {$topic}.",
      ];
    }

    return [
      "Needs additional support in {$topic}.",
      "Consider one-on-one tutoring or extra practice sessions.",
      "Break down {$topic} problems into smaller steps.",
    ];
  }

  /**
   * Get student's weakest topic.
   */
  private function getWeakestTopic(User $student): ?string
  {
    $topicAccuracy = $student->completedQuizSessions
      ->groupBy('question_type')
      ->map(function ($quizzes) {
        return $quizzes->avg('accuracy');
      });

    return $topicAccuracy->count() > 0 ? $topicAccuracy->keys()->first() : null;
  }

  /**
   * Get student's strongest topic.
   */
  private function getStrongestTopic(User $student): ?string
  {
    $topicAccuracy = $student->completedQuizSessions
      ->groupBy('question_type')
      ->map(function ($quizzes) {
        return $quizzes->avg('accuracy');
      })
      ->sortDesc();

    return $topicAccuracy->count() > 0 ? $topicAccuracy->keys()->first() : null;
  }
}
