<?php

namespace App;

enum UserRole: string
{
    case STUDENT = 'student';
    case TEACHER = 'teacher';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::STUDENT => 'Student',
            self::TEACHER => 'Teacher',
            self::ADMIN => 'Admin',
        };
    }
}
