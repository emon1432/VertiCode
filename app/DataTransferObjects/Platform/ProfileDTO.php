<?php

namespace App\DataTransferObjects\Platform;

use App\Enums\Platform;

class ProfileDTO
{
    public function __construct(
        public Platform $platform,
        public string $handle,
        public ?int $rating,
        public int $totalSolved,
        public array $raw = []
    ) {}
}
