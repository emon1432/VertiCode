<?php

namespace App\Platforms\LeetCode;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
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
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchUserProfile($handle);

        $totalSolved = collect(
            $data['submitStatsGlobal']['acSubmissionNum'] ?? []
        )->sum('count');

        return new ProfileDTO(
            platform: Platform::LEETCODE,
            handle: $data['username'],
            rating: null, // LeetCode has no stable global rating
            totalSolved: $totalSolved,
            raw: $data
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        // LeetCode public API does not expose per-problem submissions reliably
        return collect();
    }
}
