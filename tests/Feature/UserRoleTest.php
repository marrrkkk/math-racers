<?php

use App\Models\User;
use App\UserRole;

test('user can be created with student role', function () {
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

test('user can be created with teacher role', function () {
    $user = User::factory()->create([
        'role' => UserRole::TEACHER,
        'grade_level' => null,
    ]);

    expect($user->role)->toBe(UserRole::TEACHER);
    expect($user->grade_level)->toBeNull();
    expect($user->isStudent())->toBeFalse();
    expect($user->isTeacher())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});

test('user can be created with admin role', function () {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
        'grade_level' => null,
    ]);

    expect($user->role)->toBe(UserRole::ADMIN);
    expect($user->grade_level)->toBeNull();
    expect($user->isStudent())->toBeFalse();
    expect($user->isTeacher())->toBeFalse();
    expect($user->isAdmin())->toBeTrue();
});

test('student registration requires grade level', function () {
    $response = $this->post('/register', [
        'name' => 'Test Student',
        'email' => 'student@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'student',
        // Missing grade_level
    ]);

    $response->assertSessionHasErrors('grade_level');
});

test('teacher registration does not require grade level', function () {
    $response = $this->post('/register', [
        'name' => 'Test Teacher',
        'email' => 'teacher@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'teacher',
    ]);

    $response->assertRedirect('/dashboard');
    expect(User::where('email', 'teacher@test.com')->first()->grade_level)->toBeNull();
});
