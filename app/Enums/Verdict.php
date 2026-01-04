<?php

namespace App\Enums;

enum Verdict: string
{
    case ACCEPTED = 'accepted';
    case WRONG = 'wrong';
    case TLE = 'tle';
    case RUNTIME = 'runtime';
    case OTHER = 'other';
}
