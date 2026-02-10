<?php

namespace App\DataTransferObjects\Platform;

use App\Enums\ContestType;
use App\Enums\Platform;

class ContestDTO
{
    public function __construct(
        public Platform $platform,
        public string $platformContestId,
        public string $name,
        public ?string $slug,
        public ?string $description,
        public ContestType $type,
        public ?string $phase,
        public ?int $durationSeconds,
        public ?\DateTimeImmutable $startTime,
        public ?\DateTimeImmutable $endTime,
        public string $url,
        public ?int $participantCount = null,
        public bool $isRated = false,
        public array $tags = [],
        public array $raw = []
    ) {}
}
