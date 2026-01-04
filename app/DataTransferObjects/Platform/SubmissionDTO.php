<?php

namespace App\DataTransferObjects\Platform;

use App\Enums\Verdict;

class SubmissionDTO
{
    public function __construct(
        public string $problemId,
        public string $problemName,
        public ?int $difficulty,
        public Verdict $verdict,
        public \DateTimeImmutable $submittedAt
    ) {}
}
