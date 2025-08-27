<?php

namespace App\Enums;

enum QuestionType: string
{
  case ADDITION = 'addition';
  case SUBTRACTION = 'subtraction';
  case MULTIPLICATION = 'multiplication';
  case DIVISION = 'division';

  public function label(): string
  {
    return match ($this) {
      self::ADDITION => 'Addition',
      self::SUBTRACTION => 'Subtraction',
      self::MULTIPLICATION => 'Multiplication',
      self::DIVISION => 'Division',
    };
  }

  public function symbol(): string
  {
    return match ($this) {
      self::ADDITION => '+',
      self::SUBTRACTION => '-',
      self::MULTIPLICATION => '×',
      self::DIVISION => '÷',
    };
  }
}
