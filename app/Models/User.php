<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'grade_level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'grade_level' => 'integer',
        ];
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === UserRole::STUDENT;
    }

    /**
     * Check if user is a teacher
     */
    public function isTeacher(): bool
    {
        return $this->role === UserRole::TEACHER;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Get all questions created by this user (for teachers/admins).
     */
    public function createdQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    /**
     * Get all quiz sessions for this user (for students).
     */
    public function quizSessions(): HasMany
    {
        return $this->hasMany(QuizSession::class, 'student_id');
    }

    /**
     * Get all progress records for this user (for students).
     */
    public function progress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'student_id');
    }

    /**
     * Get completed quiz sessions for this user.
     */
    public function completedQuizSessions(): HasMany
    {
        return $this->quizSessions()->completed();
    }

    /**
     * Get progress for a specific question type and grade level.
     */
    public function getProgressFor(string $questionType, int $gradeLevel): ?StudentProgress
    {
        return $this->progress()
            ->where('question_type', $questionType)
            ->where('grade_level', $gradeLevel)
            ->first();
    }

    /**
     * Get total points across all topics for the user's grade level.
     */
    public function getTotalPointsAttribute(): int
    {
        if (!$this->isStudent()) {
            return 0;
        }

        return $this->progress()
            ->where('grade_level', $this->grade_level)
            ->sum('total_points');
    }

    /**
     * Get all badges earned by this student.
     */
    public function getAllBadgesAttribute(): array
    {
        if (!$this->isStudent()) {
            return [];
        }

        $allBadges = [];

        foreach ($this->progress as $progress) {
            $badges = $progress->badges_earned ?? [];
            $allBadges = array_merge($allBadges, $badges);
        }

        return $allBadges;
    }
}
