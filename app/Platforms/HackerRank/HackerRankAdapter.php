<?php

namespace App\Platforms\HackerRank;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class HackerRankAdapter implements PlatformAdapter
{
    public function __construct(
        protected HackerRankClient $client
    ) {}

    public function platform(): string
    {
        return Platform::HACKERRANK->value;
    }

    public function profileUrl(string $handle): string
    {
        return "https://www.hackerrank.com/profile/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        // ❗ HackerRank submissions are not publicly accessible
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::HACKERRANK,
            handle: $handle,
            rating: null,
            totalSolved: (int) ($data['total_solved'] ?? 0),
            raw: [
                'badges' => $data['badges'] ?? null,
                'profile' => $data['raw'] ?? [],
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect(); // not supported
    }
}
