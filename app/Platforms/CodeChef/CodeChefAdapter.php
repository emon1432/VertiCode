<?php

namespace App\Platforms\CodeChef;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class CodeChefAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
{
    public function __construct(
        protected CodeChefClient $client
    ) {}

    public function platform(): string
    {
        return Platform::CODECHEF->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "https://www.codechef.com/users/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        // CodeChef submissions require OAuth API which needs client credentials
        // Submissions are also paginated and expensive to fetch
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);
        $ratingGraph = $this->client->fetchRatingGraph($handle);

        // Build comprehensive raw data
        $rawData = [
            'handle' => $profileData['handle'],
            'rating' => $profileData['rating'],
            'max_rating' => $profileData['max_rating'],
            'stars' => $profileData['stars'],
            'country_rank' => $profileData['country_rank'],
            'global_rank' => $profileData['global_rank'],
            'fully_solved' => $profileData['fully_solved'],
            'partially_solved' => $profileData['partially_solved'],
            'badges' => $profileData['badges'],
            'rating_graph' => $ratingGraph,
            'contest_categories' => [
                'long' => count($ratingGraph['long'] ?? []),
                'cookoff' => count($ratingGraph['cookoff'] ?? []),
                'lunchtime' => count($ratingGraph['lunchtime'] ?? []),
                'starters' => count($ratingGraph['starters'] ?? []),
            ],
        ];

        return new ProfileDTO(
            platform: Platform::CODECHEF,
            handle: $profileData['handle'],
            rating: $profileData['rating'],
            totalSolved: $profileData['total_solved'],
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        // CodeChef submissions require OAuth API access
        // Would need to implement:
        // 1. OAuth client credentials flow
        // 2. Token management (1 hour TTL)
        // 3. Paginated API requests
        // 4. Rate limiting
        // For now, returning empty collection and relying on profile total_solved
        return collect();
    }

    public function supportsContests(): bool
    {
        return false;
    }

    public function fetchContests(int $limit = 100): Collection
    {
        return collect();
    }

    public function supportsProblems(): bool
    {
        return false;
    }

    public function fetchProblems(int $limit = 500, ?string $contestId = null): Collection
    {
        return collect();
    }
}
