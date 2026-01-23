<?php

namespace App\Platforms\LeetCode;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class LeetCodeAdapter implements PlatformAdapter
{
    public function __construct(
        protected LeetCodeClient $client
    ) {}

    public function platform(): string
    {
        return Platform::LEETCODE->value;
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        throw new \RuntimeException('Not implemented');
    }

    public function fetchSubmissions(string $handle): Collection
    {
        throw new \RuntimeException('Not implemented');
    }
}
