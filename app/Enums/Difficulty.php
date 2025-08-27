<?php

namespace App\Enums;

enum Difficulty: string
{
  case EASY = 'easy';
  case MEDIUM = 'medium';
  case HARD = 'hard';

  public function label(): string
  {
    return match ($this) {
      self::EASY => 'Easy',
      self::MEDIUM => 'Medium',
      self::HARD => 'Hard',
    };
  }

  public function points(): int
  {
    return match ($this) {
      self::EASY => 5,
      self::MEDIUM => 10,
      self::HARD => 15,
    };
  }
}
