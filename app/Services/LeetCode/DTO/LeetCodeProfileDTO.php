<?php

namespace App\Services\LeetCode\DTO;

class LeetCodeProfileDTO
{
    public function __construct(
        public readonly string $username,
        public readonly int $totalSolved,
        public readonly int $easySolved,
        public readonly int $mediumSolved,
        public readonly int $hardSolved,
        public readonly ?int $ranking,
        public readonly array $raw = []
    ) {}
}
