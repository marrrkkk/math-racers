<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Role-based routes
Route::middleware(['auth', 'role:student'])->group(function () {
    Route::prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\StudentController::class, 'dashboard'])->name('dashboard');
        Route::post('/select-grade', [\App\Http\Controllers\StudentController::class, 'selectGrade'])->name('select-grade');
        Route::get('/topics/{grade}', [\App\Http\Controllers\StudentController::class, 'topics'])->name('topics');
        Route::post('/quiz/start', [\App\Http\Controllers\StudentController::class, 'startQuiz'])->name('quiz.start');
        Route::get('/quiz/{session}', [\App\Http\Controllers\StudentController::class, 'quiz'])->name('quiz');
        Route::post('/quiz/{session}/answer', [\App\Http\Controllers\StudentController::class, 'submitAnswer'])->name('quiz.answer');
        Route::post('/quiz/{session}/complete', [\App\Http\Controllers\StudentController::class, 'completeQuiz'])->name('quiz.complete');
        Route::get('/progress', [\App\Http\Controllers\StudentController::class, 'progress'])->name('progress');
        Route::get('/leaderboard', [\App\Http\Controllers\StudentController::class, 'leaderboard'])->name('leaderboard');
    });
});

Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/student-performance', [\App\Http\Controllers\TeacherController::class, 'studentPerformance'])->name('student-performance');
        Route::get('/student/{student}', [\App\Http\Controllers\TeacherController::class, 'studentDetail'])->name('student.detail');
        Route::get('/class-performance', [\App\Http\Controllers\TeacherController::class, 'classPerformance'])->name('class-performance');
        Route::get('/topic-assignments', [\App\Http\Controllers\TeacherController::class, 'topicAssignments'])->name('topic-assignments');
        Route::post('/assign-topics', [\App\Http\Controllers\TeacherController::class, 'assignTopics'])->name('assign-topics');
        Route::resource('questions', \App\Http\Controllers\QuestionController::class);
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'userManagement'])->name('users');
        Route::patch('/users/{user}/status', [\App\Http\Controllers\AdminController::class, 'updateUserStatus'])->name('users.status');
        Route::patch('/users/{user}/role', [\App\Http\Controllers\AdminController::class, 'updateUserRole'])->name('users.role');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/system-logs', [\App\Http\Controllers\AdminController::class, 'systemLogs'])->name('system-logs');
        Route::get('/question-bank', [\App\Http\Controllers\AdminController::class, 'questionBankManagement'])->name('question-bank');
        Route::delete('/questions/bulk-delete', [\App\Http\Controllers\AdminController::class, 'bulkDeleteQuestions'])->name('questions.bulk-delete');
        Route::get('/questions/export', [\App\Http\Controllers\AdminController::class, 'exportQuestions'])->name('questions.export');
    });
});

require __DIR__ . '/auth.php';
