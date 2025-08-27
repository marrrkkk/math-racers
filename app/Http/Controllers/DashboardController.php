<?php

namespace App\Http\Controllers;

use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            UserRole::STUDENT => $this->studentDashboard($user),
            UserRole::TEACHER => $this->teacherDashboard($user),
            UserRole::ADMIN => $this->adminDashboard($user),
        };
    }

    private function studentDashboard($user)
    {
        return redirect()->route('student.dashboard');
    }

    private function teacherDashboard($user)
    {
        return redirect()->route('teacher.dashboard');
    }

    private function adminDashboard($user)
    {
        return redirect()->route('admin.dashboard');
    }
}
