<?php

namespace App\Platforms\CodeChef;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ContestDTO;
use App\DataTransferObjects\Platform\ProblemDTO;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\ContestType;
use App\Enums\Difficulty;
use App\Enums\Platform;
use App\Services\Platforms\CodeChefDataCollector;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CodeChefAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
{
    private CodeChefDataCollector $collector;

    public function __construct(
        protected CodeChefClient $client
    ) {
        $this->collector = new CodeChefDataCollector();
    }

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
        return true;
    }

    public function fetchContests(int $limit = 100): Collection
    {
        try {
            Log::info("CodeChef: Fetching contests with limit: $limit");

            $contests = $this->collector->collectContests($limit);

            if ($contests->isEmpty()) {
                Log::warning("CodeChef: No contests available from collector");
                return collect();
            }

            return $contests
                ->map(function ($contest) {
                    $contestId = (string) $contest['id'];
                    $startTime = isset($contest['startTime'])
                        ? CarbonImmutable::createFromTimestamp($contest['startTime'])
                        : null;

                    $endTime = isset($contest['endTime'])
                        ? CarbonImmutable::createFromTimestamp($contest['endTime'])
                        : null;

                    // Map contest type
                    $type = match($contest['type']) {
                        'upcoming', 'present' => ContestType::CONTEST,
                        'past' => ContestType::CONTEST,
                        default => ContestType::PRACTICE,
                    };

                    $phase = match($contest['type']) {
                        'upcoming' => 'before',
                        'present' => 'running',
                        'past' => 'finished',
                        default => 'finished',
                    };

                    return new ContestDTO(
                        platform: Platform::CODECHEF,
                        platformContestId: $contestId,
                        name: $contest['name'],
                        slug: 'contest-' . $contestId,
                        description: null,
                        type: $type,
                        phase: $phase,
                        durationSeconds: $contest['durationSeconds'] ?? null,
                        startTime: $startTime,
                        endTime: $endTime,
                        url: "https://www.codechef.com/{$contestId}",
                        participantCount: null,
                        isRated: true,
                        tags: [],
                        raw: $contest
                    );
                })
                ->filter()
                ->tap(fn($collection) => Log::info("CodeChef: Processed {$collection->count()} contests for sync"));
        } catch (\Exception $e) {
            Log::error("CodeChef fetchContests failed: {$e->getMessage()}");
            return collect();
        }
    }

    public function supportsProblems(): bool
    {
        return true;
    }

    public function fetchProblems(int $limit = 200, ?string $contestId = null): Collection
    {
        try {
            Log::info("CodeChef: Fetching problems with limit: $limit", ['contestId' => $contestId]);

            $problems = $this->collector->collectProblems($limit);

            if ($problems->isEmpty()) {
                Log::warning("CodeChef: No problems available from collector");
                return collect();
            }

            $filtered = $problems
                ->filter(fn($problem) => !$contestId || ($problem['contestCode'] ?? null) === $contestId)
                ->map(function ($problem) {
                    // Calculate accuracy
                    $totalSubmissions = $problem['totalSubmissions'] ?? 0;
                    $successfulSubmissions = $problem['successfulSubmissions'] ?? 0;
                    $accuracy = $totalSubmissions > 0
                        ? ($successfulSubmissions / $totalSubmissions) * 100
                        : null;

                    // Map difficulty based on rating (if available), otherwise use accuracy
                    $rating = $problem['difficultyRating'];
                    $difficulty = match(true) {
                        $rating !== null && $rating < 1200 => Difficulty::EASY,
                        $rating !== null && $rating < 1600 => Difficulty::MEDIUM,
                        $rating !== null => Difficulty::HARD,
                        $accuracy !== null && $accuracy > 70 => Difficulty::EASY,
                        $accuracy !== null && $accuracy > 40 => Difficulty::MEDIUM,
                        $accuracy !== null => Difficulty::HARD,
                        default => Difficulty::UNKNOWN,
                    };

                    // Store contest code in raw data for later association
                    $contestCode = $problem['contestCode'];
                    $tags = [];
                    if ($contestCode) {
                        $tags[] = $contestCode;
                    }

                    return new ProblemDTO(
                        platform: Platform::CODECHEF,
                        platformProblemId: $problem['id'],
                        name: $problem['name'],
                        slug: 'problem-' . $problem['id'],
                        code: $problem['code'],
                        description: null,
                        difficulty: $difficulty,
                        rating: $rating,
                        points: null,
                        accuracy: $accuracy,
                        timeLimitMs: null,
                        memoryLimitMb: null,
                        totalSubmissions: $totalSubmissions,
                        acceptedSubmissions: $successfulSubmissions,
                        solvedCount: $problem['distinctSuccessfulSubmissions'] ?? $successfulSubmissions,
                        tags: $tags,
                        topics: [],
                        url: "https://www.codechef.com/problems/{$problem['code']}",
                        editorialUrl: null,
                        contestId: $contestCode, // Store contest code for linking
                        isPremium: false,
                        raw: $problem
                    );
                })
                ->take($limit);

            Log::info("CodeChef: Processed {$filtered->count()} problems for sync");
            return $filtered;
        } catch (\Exception $e) {
            Log::error("CodeChef fetchProblems failed: {$e->getMessage()}");
            return collect();
        }
    }
}
