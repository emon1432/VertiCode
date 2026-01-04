<?php

namespace App\Enums;

enum Difficulty: string
{
    case EASY = 'easy';
    case MEDIUM = 'medium';
    case HARD = 'hard';
    case UNKNOWN = 'unknown';
}
