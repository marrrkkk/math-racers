<?php

namespace App\Http\Controllers;

use App\Enums\Difficulty;
use App\Enums\QuestionType;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuestionController extends Controller
{
  /**
   * Display a listing of questions for teachers.
   */
  public function index(Request $request)
  {
    $query = Question::with('creator')
      ->orderBy('created_at', 'desc');

    // Apply filters
    if ($request->filled('grade_level')) {
      $query->forGrade($request->grade_level);
    }

    if ($request->filled('question_type')) {
      $query->ofType(QuestionType::from($request->question_type));
    }

    if ($request->filled('difficulty')) {
      $query->withDifficulty(Difficulty::from($request->difficulty));
    }

    if ($request->filled('search')) {
      $query->where(function ($q) use ($request) {
        $q->where('question_text', 'like', '%' . $request->search . '%')
          ->orWhere('deped_competency', 'like', '%' . $request->search . '%');
      });
    }

    $questions = $query->paginate(15)->withQueryString();

    return Inertia::render('Teacher/Questions/Index', [
      'questions' => $questions,
      'filters' => $request->only(['grade_level', 'question_type', 'difficulty', 'search']),
      'questionTypes' => collect(QuestionType::cases())->map(fn($type) => [
        'value' => $type->value,
        'label' => $type->label(),
      ]),
      'difficulties' => collect(Difficulty::cases())->map(fn($difficulty) => [
        'value' => $difficulty->value,
        'label' => $difficulty->label(),
      ]),
      'gradeLevels' => [
        ['value' => 1, 'label' => 'Grade 1'],
        ['value' => 2, 'label' => 'Grade 2'],
        ['value' => 3, 'label' => 'Grade 3'],
      ],
    ]);
  }

  /**
   * Show the form for creating a new question.
   */
  public function create()
  {
    return Inertia::render('Teacher/Questions/Create', [
      'questionTypes' => collect(QuestionType::cases())->map(fn($type) => [
        'value' => $type->value,
        'label' => $type->label(),
        'symbol' => $type->symbol(),
      ]),
      'difficulties' => collect(Difficulty::cases())->map(fn($difficulty) => [
        'value' => $difficulty->value,
        'label' => $difficulty->label(),
        'points' => $difficulty->points(),
      ]),
      'gradeLevels' => [
        ['value' => 1, 'label' => 'Grade 1'],
        ['value' => 2, 'label' => 'Grade 2'],
        ['value' => 3, 'label' => 'Grade 3'],
      ],
      'depedCompetencies' => $this->getDepedCompetencies(),
    ]);
  }

  /**
   * Store a newly created question in storage.
   */
  public function store(StoreQuestionRequest $request)
  {
    $validated = $request->validated();

    $question = Question::create([
      ...$validated,
      'created_by' => Auth::id(),
    ]);

    return redirect()->route('teacher.questions.index')
      ->with('success', 'Question created successfully!');
  }

  /**
   * Display the specified question.
   */
  public function show(Question $question)
  {
    $question->load('creator');

    return Inertia::render('Teacher/Questions/Show', [
      'question' => $question,
    ]);
  }

  /**
   * Show the form for editing the specified question.
   */
  public function edit(Question $question)
  {
    return Inertia::render('Teacher/Questions/Edit', [
      'question' => $question,
      'questionTypes' => collect(QuestionType::cases())->map(fn($type) => [
        'value' => $type->value,
        'label' => $type->label(),
        'symbol' => $type->symbol(),
      ]),
      'difficulties' => collect(Difficulty::cases())->map(fn($difficulty) => [
        'value' => $difficulty->value,
        'label' => $difficulty->label(),
        'points' => $difficulty->points(),
      ]),
      'gradeLevels' => [
        ['value' => 1, 'label' => 'Grade 1'],
        ['value' => 2, 'label' => 'Grade 2'],
        ['value' => 3, 'label' => 'Grade 3'],
      ],
      'depedCompetencies' => $this->getDepedCompetencies(),
    ]);
  }

  /**
   * Update the specified question in storage.
   */
  public function update(UpdateQuestionRequest $request, Question $question)
  {
    $validated = $request->validated();

    $question->update($validated);

    return redirect()->route('teacher.questions.index')
      ->with('success', 'Question updated successfully!');
  }

  /**
   * Remove the specified question from storage.
   */
  public function destroy(Question $question)
  {
    // Check if question is used in any quiz sessions
    if ($question->quizAnswers()->exists()) {
      return back()->with('error', 'Cannot delete question that has been used in quizzes.');
    }

    $question->delete();

    return redirect()->route('teacher.questions.index')
      ->with('success', 'Question deleted successfully!');
  }

  /**
   * Get DepEd competencies for each grade level and question type.
   */
  private function getDepedCompetencies(): array
  {
    return [
      1 => [
        'addition' => [
          'Add whole numbers up to 100 without regrouping',
          'Add whole numbers up to 100 with regrouping',
          'Solve routine and non-routine problems involving addition',
        ],
        'subtraction' => [
          'Subtract whole numbers up to 100 without regrouping',
          'Subtract whole numbers up to 100 with regrouping',
          'Solve routine and non-routine problems involving subtraction',
        ],
        'multiplication' => [
          'Visualize and represent multiplication of numbers 1-10',
          'Multiply numbers by 2, 5, and 10',
        ],
        'division' => [
          'Visualize and represent division of numbers 1-10',
          'Divide numbers by 2, 5, and 10',
        ],
      ],
      2 => [
        'addition' => [
          'Add 2- to 3-digit numbers without regrouping',
          'Add 2- to 3-digit numbers with regrouping',
          'Solve multi-step word problems involving addition',
        ],
        'subtraction' => [
          'Subtract 2- to 3-digit numbers without regrouping',
          'Subtract 2- to 3-digit numbers with regrouping',
          'Solve multi-step word problems involving subtraction',
        ],
        'multiplication' => [
          'Multiply 2-digit by 1-digit numbers without regrouping',
          'Multiply 2-digit by 1-digit numbers with regrouping',
          'Solve word problems involving multiplication',
        ],
        'division' => [
          'Divide 2- to 3-digit numbers by 1-digit numbers',
          'Find quotient and remainder in division',
          'Solve word problems involving division',
        ],
      ],
      3 => [
        'addition' => [
          'Add 3- to 4-digit numbers with regrouping',
          'Estimate sums to the nearest tens and hundreds',
          'Solve complex word problems involving addition',
        ],
        'subtraction' => [
          'Subtract 3- to 4-digit numbers with regrouping',
          'Estimate differences to the nearest tens and hundreds',
          'Solve complex word problems involving subtraction',
        ],
        'multiplication' => [
          'Multiply 3-digit by 1-digit numbers',
          'Multiply 2-digit by 2-digit numbers',
          'Solve complex word problems involving multiplication',
        ],
        'division' => [
          'Divide 3- to 4-digit numbers by 1-digit numbers',
          'Divide 2-digit by 2-digit numbers',
          'Solve complex word problems involving division',
        ],
      ],
    ];
  }
}
