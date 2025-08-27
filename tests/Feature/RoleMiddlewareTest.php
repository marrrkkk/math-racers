<?php

use App\Models\User;
use App\UserRole;

test('unauthenticated user is redirected to login', function () {
    // Create a test route with role middleware
    Route::get('/test-student', function () {
        return 'Student Area';
    })->middleware(['role:student']);

    $response = $this->get('/test-student');
    $response->assertRedirect('/login');
});

test('student can access student routes', function () {
    $student = User::factory()->create(['role' => UserRole::STUDENT]);

    Route::get('/test-student', function () {
        return 'Student Area';
    })->middleware(['role:student']);

    $response = $this->actingAs($student)->get('/test-student');
    $response->assertStatus(200);
    $response->assertSeeText('Student Area');
});

test('teacher cannot access student-only routes', function () {
    $teacher = User::factory()->create(['role' => UserRole::TEACHER]);

    Route::get('/test-student', function () {
        return 'Student Area';
    })->middleware(['role:student']);

    $response = $this->actingAs($teacher)->get('/test-student');
    $response->assertStatus(403);
});

test('admin can access admin routes', function () {
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    Route::get('/test-admin', function () {
        return 'Admin Area';
    })->middleware(['role:admin']);

    $response = $this->actingAs($admin)->get('/test-admin');
    $response->assertStatus(200);
    $response->assertSeeText('Admin Area');
});

test('middleware accepts multiple roles', function () {
    $teacher = User::factory()->create(['role' => UserRole::TEACHER]);
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);

    Route::get('/test-multi', function () {
        return 'Multi Role Area';
    })->middleware(['role:teacher,admin']);

    // Teacher should have access
    $response = $this->actingAs($teacher)->get('/test-multi');
    $response->assertStatus(200);

    // Admin should have access
    $response = $this->actingAs($admin)->get('/test-multi');
    $response->assertStatus(200);
});
