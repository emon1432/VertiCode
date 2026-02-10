<?php

namespace App\Enums;

enum ContestType: string
{
    case CONTEST = 'contest';
    case PRACTICE = 'practice';
    case CHALLENGE = 'challenge';
    case VIRTUAL = 'virtual';
    case RATED = 'rated';
    case UNRATED = 'unrated';
}
