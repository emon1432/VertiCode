<?php

namespace App\Platforms\AtCoder;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ContestDTO;
use App\DataTransferObjects\Platform\ProblemDTO;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\ContestType;
use App\Enums\Difficulty;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\Carbon;
use App\Services\Platforms\AtCoderDataCollector;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * AtCoder Platform Adapter
 *
 * Professional synchronization adapter for AtCoder using independently scraped data.
 *
 * Architecture - Building Our Own:
 * - We scrape data directly from atcoder.jp
 * - ZERO dependency on external APIs (only atcoder.jp)
 * - Full control over our data pipeline
 * - Can operate independently even if all external services are down
 *
 * How We Build This (Independently from atcoder.jp):
 * 1. AtCoderDataCollector scrapes atcoder.jp/contests
 * 2. For each contest, scrapes atcoder.jp/contests/{id}/tasks
 * 3. Parses HTML to extract contest/problem data
 * 4. Stores everything in our own database
 * 5. Adapter reads from our database (no external calls)
 *
 * Why We Don't Use Third-Party APIs:
 * ❌ Don't use any external APIs
 * ❌ Don't use GitHub-hosted JSON backups
 * ❌ Don't use AtCoder's unofficial APIs
 * ✅ Only scrape atcoder.jp directly
 * ✅ Store in our own database
 * ✅ Serve from our own infrastructure
 *
 * Features:
 * - Independently scraped contests with metadata
 * - Independently scraped problems with constraints
 * - No external API dependency
 * - Rate-limited respectful scraping
 * - Graceful fallback to cached data
 */
class AtCoderAdapter implements ContestSyncAdapter, ProblemSyncAdapter, PlatformAdapter
{
    private AtCoderDataCollector $collector;
    private AtCoderClient $client;

    /**
     * Constructor - Initialize data collector and client.
     */
    public function __construct()
    {
        $this->collector = new AtCoderDataCollector();
        $this->client = new AtCoderClient();
    }

    /**
     * Get the platform identifier.
     */
    public function platform(): string
    {
        return Platform::ATCODER->value;
    }

    /**
     * Get the platform profile URL for a given handle.
     */
    public function profileUrl(string $handle): string
    {
        return "https://atcoder.jp/users/{$handle}";
    }

    /**
     * Check if adapter supports fetching user submissions.
     */
    public function supportsSubmissions(): bool
    {
        return true;
    }

    /**
     * Fetch user profile from AtCoder.
     */
    public function fetchProfile(string $handle): ProfileDTO
    {
        try {
            $profileData = $this->client->fetchProfile($handle);

            return new ProfileDTO(
                platform: Platform::ATCODER,
                handle: $profileData['handle'],
                rating: $profileData['rating'],
                totalSolved: $profileData['total_solved'],
                raw: $profileData
            );
        } catch (\Exception $e) {
            Log::error("Failed to fetch AtCoder profile for {$handle}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Fetch user submissions from AtCoder.
     */
    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);

            return collect($submissions)
                ->filter(fn($sub) => ($sub['verdict'] ?? null) === 'AC')
                ->map(function ($sub) {
                    // Parse problem_id (e.g., 'abc123_a')
                    $problemId = $sub['problem_id'] ?? 'unknown';

                    return new SubmissionDTO(
                        problemId: $problemId,
                        problemName: $problemId,
                        difficulty: null,
                        verdict: Verdict::ACCEPTED,
                        submittedAt: new \DateTimeImmutable($sub['timestamp'] ?? 'now'),
                        raw: $sub
                    );
                });
        } catch (\Exception $e) {
            Log::error("Failed to fetch AtCoder submissions for {$handle}", ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Check if adapter supports contest syncing.
     */
    public function supportsContests(): bool
    {
        return true;
    }

    /**
     * Check if adapter supports problem syncing.
     */
    public function supportsProblems(): bool
    {
        return true;
    }

    /**
     * Fetch contests from AtCoder.
     *
     * Uses independent data collector to gather contest data,
     * removing dependency on external APIs.
     *
     * @param int $limit Maximum number of contests to fetch
     * @return Collection<int, ContestDTO>
     */
    public function fetchContests(int $limit = 100): Collection
    {
        try {
            // Collect contests using independent data collector with limit
            $contests = $this->collector->collectContests($limit);

            if ($contests->isEmpty()) {
                Log::warning("AtCoder: No contests available from collector (limit: $limit)");
                return collect();
            }

            return $contests
                ->map(fn($contest) => $this->mapContestDTO($contest))
                ->filter()
                ->tap(fn($collection) => Log::info("AtCoder: Processed {$collection->count()} contests for sync (limit: $limit)"));
        } catch (\Exception $e) {
            Log::error('AtCoder contests fetch failed', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Fetch problems from AtCoder.
     *
     * Uses independent data collector to gather problem data with difficulty ratings.
     * Optionally filters by contest ID.
     *
     * @param int $limit Maximum number of problems to fetch
     * @param string|null $contestId Optional contest ID to filter problems
     * @return Collection<int, ProblemDTO>
     */
    public function fetchProblems(int $limit = 200, ?string $contestId = null): Collection
    {
        try {
            // Collect problems using independent data collector with limit
            $problems = $this->collector->collectProblems($limit);

            if ($problems->isEmpty()) {
                Log::warning("AtCoder: No problems available from collector (limit: $limit)");
                return collect();
            }

            // Get contest-problem pairs if filtering by contest
            $contestProblems = [];
            if ($contestId) {
                $pairs = $this->collector->collectContestProblemPairs();
                $contestProblems = $pairs->toArray();
            }

            return $problems
                ->when($contestId, fn($problems) => $problems->filter(
                    fn($problem) => $this->isProblemInContest(
                        $problem['id'] ?? null,
                        $contestId,
                        $contestProblems
                    )
                ))
                ->map(fn($problem) => $this->mapProblemDTO($problem))
                ->filter()
                ->tap(fn($collection) => Log::info("AtCoder: Processed {$collection->count()} problems for sync (limit: $limit)", [
                    'contest_filter' => $contestId,
                ]));
        } catch (\Exception $e) {
            Log::error('AtCoder problems fetch failed', ['error' => $e->getMessage()]);

            return collect();
        }
    }

    /**
     * Map AtCoder contest data to ContestDTO.
     *
     * Transforms raw API response into standardized DTO with proper type conversions
     * and error handling.
     *
     * @param array $data Raw contest data from API
     * @return ContestDTO|null Mapped DTO or null on failure
     */
    private function mapContestDTO(array $data): ?ContestDTO
    {
        $id = $data['id'] ?? null;
        $name = $data['title'] ?? null;

        if (!$id || !$name) {
            return null;
        }

        try {
            // Parse Unix timestamps to Carbon instances
            $startTime = isset($data['start_epoch_second'])
                ? CarbonImmutable::createFromTimestamp($data['start_epoch_second'])
                : null;

            $endTime = isset($data['start_epoch_second'], $data['duration'])
                ? CarbonImmutable::createFromTimestamp($data['start_epoch_second'] + $data['duration'])
                : null;

            return new ContestDTO(
                platform: Platform::ATCODER,
                platformContestId: (string) $id,
                name: $name,
                slug: $this->slugify($id),
                description: $data['description'] ?? null,
                type: $this->mapContestType($data),
                phase: 'finished', // AtCoder API only provides finished contests
                durationSeconds: (int) ($data['duration'] ?? 0),
                startTime: $startTime,
                endTime: $endTime,
                url: "https://atcoder.jp/contests/{$id}",
                participantCount: $this->extractParticipantCount($data),
                isRated: (bool) ($data['is_rated'] ?? false),
                tags: $this->extractContestTags($data),
                raw: $data,
            );
        } catch (\Exception $e) {
            Log::warning('Failed to map AtCoder contest', [
                'contest_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Map AtCoder problem data to ProblemDTO.
     *
     * Transforms raw API response including ML-estimated difficulty into standardized DTO.
     * Difficulty is extracted from problem_models array which contains ML-based estimations.
     *
     * @param array $data Raw problem data from merged-problems API
     * @return ProblemDTO|null Mapped DTO or null on failure
     */
    private function mapProblemDTO(array $data): ?ProblemDTO
    {
        $id = $data['id'] ?? null;
        $title = $data['title'] ?? null;

        if (!$id || !$title) {
            return null;
        }

        try {
            // Extract contest ID from problem ID (format: abc123_a -> abc123)
            $contestId = $this->extractContestIdFromProblem($id);

            return new ProblemDTO(
                platform: Platform::ATCODER,
                platformProblemId: (string) $id,
                name: $title,
                slug: $this->slugify($id),
                code: $this->extractProblemCode($id),
                description: null, // Would require HTML scraping to get detailed descriptions
                difficulty: $this->mapDifficulty($data),
                rating: $data['point'] ?? null,
                points: (float) ($data['point'] ?? 0),
                accuracy: null,
                timeLimitMs: null,
                memoryLimitMb: null,
                totalSubmissions: 0,
                acceptedSubmissions: 0,
                solvedCount: 0,
                tags: $this->extractProblemTags($data),
                topics: [],
                url: "https://atcoder.jp/contests/{$contestId}/tasks/{$id}",
                editorialUrl: "https://atcoder.jp/contests/{$contestId}/editorial",
                contestId: $contestId,
                isPremium: false,
                raw: $data,
            );
        } catch (\Exception $e) {
            Log::warning('Failed to map AtCoder problem', [
                'problem_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check if problem belongs to a specific contest using the contest-problem mapping.
     *
     * @param string|null $problemId Problem ID to check
     * @param string $contestId Target contest ID
     * @param array $contestProblems Contest-problem pair mappings from API
     * @return bool True if problem is in contest
     */
    private function isProblemInContest(?string $problemId, string $contestId, array $contestProblems): bool
    {
        if (!$problemId) {
            return false;
        }

        foreach ($contestProblems as $pair) {
            if (($pair['problem_id'] ?? null) === $problemId &&
                ($pair['contest_id'] ?? null) === $contestId
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch data from AtCoder using independent collector.
     *
     * This method is deprecated in favor of direct collector access.
     * Kept for compatibility but not used.
     *
     * @deprecated Use $this->collector directly
     * @param string $endpoint API endpoint name
     * @return mixed Decoded JSON response or null on error
     */
    private function fetchFromApi(string $endpoint): mixed
    {
        // This method is no longer used - collector handles all data gathering
        // Keeping for reference/compatibility
        return null;
    }

    /**
     * Map contest type based on contest metadata.
     *
     * Uses contest name and properties to determine the appropriate contest type.
     * Defaults to CONTEST for regular AtCoder contests.
     *
     * @param array $data Contest data
     * @return ContestType Contest type enum
     */
    private function mapContestType(array $data): ContestType
    {
        $title = strtolower($data['title'] ?? '');

        // Map based on contest series/pattern
        return match (true) {
            str_contains($title, 'typical') => ContestType::CHALLENGE,
            str_contains($title, 'regular') => ContestType::CONTEST,
            str_contains($title, 'joi') => ContestType::CHALLENGE,
            str_contains($title, 'arc') => ContestType::CONTEST,
            str_contains($title, 'agc') => ContestType::CONTEST,
            str_contains($title, 'abc') => ContestType::CONTEST,
            default => ContestType::CONTEST,
        };
    }

    /**
     * Map difficulty level from ML-estimated difficulty rating.
     *
     * AtCoder provides difficulty estimations ranging from 0-4000+ based on
     * machine learning analysis of submission patterns and timing.
     *
     * Mapping:
     * - 0-400: Easy (green in AtCoder)
     * - 401-800: Medium (cyan in AtCoder)
     * - 801-1600: Hard (blue in AtCoder)
     * - 1600+: Advanced (orange/red in AtCoder)
     *
     * @param array $data Problem data with problem_models array
     * @return Difficulty|null Mapped difficulty enum or null if not available
     */
    private function mapDifficulty(array $data): ?Difficulty
    {
        $models = $data['problem_models'] ?? [];

        // problem_models contains ML-estimated difficulty for each problem
        if (empty($models) || !isset($models[0]['difficulty'])) {
            return null;
        }

        $difficulty = (int) $models[0]['difficulty'];

        // Map AtCoder's 0-4000+ difficulty scale to our Difficulty enum
        return match (true) {
            $difficulty <= 400 => Difficulty::EASY,
            $difficulty <= 800 => Difficulty::MEDIUM,
            $difficulty <= 1600 => Difficulty::HARD,
            default => Difficulty::HARD, // Use HARD for very difficult problems
        };
    }

    /**
     * Extract tags from problem data.
     *
     * Tags indicate special properties like experimental problems or special markers.
     *
     * @param array $data Problem data
     * @return array List of problem tags
     */
    private function extractProblemTags(array $data): array
    {
        $tags = [];

        $models = $data['problem_models'] ?? [];
        if (!empty($models) && ($models[0]['is_experimental'] ?? false)) {
            $tags[] = 'experimental';
        }

        return $tags;
    }

    /**
     * Extract tags from contest data.
     *
     * Tags indicate contest properties like rated/unrated status.
     *
     * @param array $data Contest data
     * @return array List of contest tags
     */
    private function extractContestTags(array $data): array
    {
        $tags = [];

        if ($data['is_rated'] ?? false) {
            $tags[] = 'rated';
        }

        return $tags;
    }

    /**
     * Extract contest ID from problem ID.
     *
     * AtCoder problem IDs follow the format: {contestId}_{problemCode}
     * Example: abc123_a -> abc123
     *
     * @param string $problemId Full problem ID
     * @return string|null Contest ID portion
     */
    private function extractContestIdFromProblem(string $problemId): ?string
    {
        $parts = explode('_', $problemId);

        return $parts[0] ?? null;
    }

    /**
     * Extract problem code letter from problem ID.
     *
     * AtCoder problem IDs follow the format: {contestId}_{problemCode}
     * Example: abc123_a -> A
     *
     * @param string $problemId Full problem ID
     * @return string Problem code in uppercase
     */
    private function extractProblemCode(string $problemId): string
    {
        $parts = explode('_', $problemId);
        $code = $parts[1] ?? 'A';

        return strtoupper($code);
    }

    /**
     * Extract participant count from contest data.
     *
     * Attempts to extract the number of participants from available fields.
     *
     * @param array|null $data Contest data
     * @return int|null Participant count or null if unavailable
     */
    private function extractParticipantCount(?array $data): ?int
    {
        if (!$data) {
            return null;
        }

        // Try to find participant count from various possible fields
        if (isset($data['participants'])) {
            return (int) $data['participants'];
        }

        return null;
    }

    /**
     * Convert string to URL-friendly slug.
     *
     * Converts to lowercase and replaces non-alphanumeric characters with hyphens.
     *
     * @param string $text Text to slugify
     * @return string URL-friendly slug
     */
    private function slugify(string $text): string
    {
        return strtolower(trim(
            (string) preg_replace('/[^A-Za-z0-9-]+/', '-', $text),
            '-'
        ));
    }
}
