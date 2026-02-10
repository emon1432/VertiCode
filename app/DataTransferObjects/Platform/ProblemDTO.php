<?php

namespace App\DataTransferObjects\Platform;

use App\Enums\Difficulty;
use App\Enums\Platform;

class ProblemDTO
{
    public function __construct(
        public Platform $platform,
        public string $platformProblemId,
        public string $name,
        public ?string $slug,
        public ?string $code,
        public ?string $description,
        public ?Difficulty $difficulty,
        public ?int $rating,
        public ?float $points,
        public ?float $accuracy,
        public ?int $timeLimitMs,
        public ?int $memoryLimitMb,
        public int $totalSubmissions = 0,
        public int $acceptedSubmissions = 0,
        public int $solvedCount = 0,
        public array $tags = [],
        public array $topics = [],
        public string $url = '',
        public ?string $editorialUrl = null,
        public ?string $contestId = null,
        public bool $isPremium = false,
        public array $raw = []
    ) {}
}
