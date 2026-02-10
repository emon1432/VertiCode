<?php

namespace App\Platforms\Codeforces;

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
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CodeforcesAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
{
    public function __construct(
        protected CodeforcesClient $client
    ) {}

    public function platform(): string
    {
        return Platform::CODEFORCES->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchUserInfo($handle);
        return "https://codeforces.com/profile/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $userInfo = $this->client->fetchUserInfo($handle);

        // Calculate max rating
        $maxRating = $this->client->calculateMaxRating($userInfo);

        // Fetch contest history for rating graph data
        $contestHistory = $this->client->fetchContestHistory($handle);

        // Fetch problem tags (cached)
        $problemTags = $this->client->fetchProblemTags();

        // Build comprehensive raw data
        $rawData = [
            'user_info' => $userInfo,
            'max_rating' => $maxRating,
            'contest_history' => $contestHistory,
            'rating_graph_data' => $this->buildRatingGraphData($contestHistory),
            'problem_tags_count' => count($problemTags),
            'rank' => $userInfo['rank'] ?? null,
            'max_rank' => $userInfo['maxRank'] ?? null,
            'contribution' => $userInfo['contribution'] ?? null,
            'friend_count' => $userInfo['friendOfCount'] ?? 0,
            'avatar' => $userInfo['avatar'] ?? null,
            'title_photo' => $userInfo['titlePhoto'] ?? null,
        ];

        return new ProfileDTO(
            platform: Platform::CODEFORCES,
            handle: $userInfo['handle'],
            rating: $userInfo['rating'] ?? null,
            totalSolved: 0, // will be calculated from submissions
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);
            $problemTags = $this->client->fetchProblemTags();

            return collect($submissions)
                ->filter(fn($sub) => ($sub['verdict'] ?? null) === 'OK')
                ->map(function ($sub) use ($problemTags) {
                    $problem = $sub['problem'];
                    $contestId = $problem['contestId'] ?? 0;
                    $index = $problem['index'];
                    $problemId = $contestId . $index;

                    // Get tags for this problem
                    $tags = $problemTags[$problemId]['tags'] ?? [];

                    return new SubmissionDTO(
                        problemId: $problemId,
                        problemName: $problem['name'],
                        difficulty: $problem['rating'] ?? null,
                        verdict: Verdict::ACCEPTED,
                        submittedAt: CarbonImmutable::createFromTimestamp(
                            $sub['creationTimeSeconds']
                        ),
                        raw: [
                            'contest_id' => $contestId,
                            'index' => $index,
                            'language' => $sub['programmingLanguage'] ?? '',
                            'time_consumed_ms' => $sub['timeConsumedMillis'] ?? 0,
                            'memory_consumed_bytes' => $sub['memoryConsumedBytes'] ?? 0,
                            'submission_id' => $sub['id'],
                            'submission_url' => $this->client->getSubmissionUrl($contestId, $sub['id']),
                            'problem_url' => $this->client->getProblemUrl($contestId, $index),
                            'tags' => $tags,
                        ]
                    );
                });
        } catch (\Exception $e) {
            Log::error("Codeforces fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Build rating graph data from contest history
     * Format: [[timestamp, rating], ...]
     */
    private function buildRatingGraphData(array $contestHistory): array
    {
        $graphData = [];

        foreach ($contestHistory as $contest) {
            if ($contest['new_rating'] !== null) {
                // We don't have exact timestamp from scraping, but we can use the rating change
                $graphData[] = [
                    'contest_id' => $contest['contest_id'],
                    'contest_name' => $contest['contest_name'],
                    'rating' => $contest['new_rating'],
                    'rating_change' => $contest['rating_change'],
                    'rank' => $contest['rank'],
                    'solved_count' => $contest['solved_count'],
                ];
            }
        }

        // Reverse to get chronological order (scraping returns newest first)
        return array_reverse($graphData);
    }

    public function supportsContests(): bool
    {
        return true;
    }

    public function fetchContests(int $limit = 100): Collection
    {
        try {
            $contests = $this->client->fetchContestList();

            return collect($contests)
                ->take($limit)
                ->map(function ($contest) {
                    $contestId = (string) $contest['id'];
                    $startTime = isset($contest['startTimeSeconds'])
                        ? CarbonImmutable::createFromTimestamp($contest['startTimeSeconds'])
                        : null;

                    $durationSeconds = $contest['durationSeconds'] ?? null;
                    $endTime = $startTime && $durationSeconds
                        ? $startTime->addSeconds($durationSeconds)
                        : null;

                    // Determine contest type based on CF data
                    $type = match($contest['type'] ?? 'CF') {
                        'CF' => ContestType::CONTEST,
                        'ICPC' => ContestType::CONTEST,
                        'IOI' => ContestType::CONTEST,
                        default => ContestType::PRACTICE,
                    };

                    return new ContestDTO(
                        platform: Platform::CODEFORCES,
                        platformContestId: $contestId,
                        name: $contest['name'],
                        slug: 'contest-' . $contestId,
                        description: null,
                        type: $type,
                        phase: strtolower($contest['phase'] ?? 'finished'),
                        durationSeconds: $durationSeconds,
                        startTime: $startTime,
                        endTime: $endTime,
                        url: "https://codeforces.com/contest/{$contestId}",
                        participantCount: null,
                        isRated: str_contains($contest['name'], 'Rated') || ($contest['phase'] ?? '') === 'FINISHED',
                        tags: [],
                        raw: $contest
                    );
                });
        } catch (\Exception $e) {
            Log::error("Codeforces fetchContests failed: {$e->getMessage()}");
            return collect();
        }
    }

    public function supportsProblems(): bool
    {
        return true;
    }

    public function fetchProblems(int $limit = 500, ?string $contestId = null): Collection
    {
        try {
            // Use the existing fetchProblemTags which already calls problemset.problems API
            $problemTagsMap = $this->client->fetchProblemTags();

            return collect($problemTagsMap)
                ->map(function ($data, $problemId) use ($contestId) {
                    // Extract contestId and index from problemId (e.g., "1000A" -> contest=1000, index="A")
                    preg_match('/^(\d+)([A-Z]\d*)$/', $problemId, $matches);
                    if (count($matches) < 3) {
                        return null;
                    }

                    $cfContestId = $matches[1];
                    $index = $matches[2];

                    // Filter by contestId if specified
                    if ($contestId && $cfContestId != $contestId) {
                        return null;
                    }

                    // Map rating to difficulty
                    $rating = $data['rating'] ?? null;
                    $difficulty = match(true) {
                        $rating === null => Difficulty::UNKNOWN,
                        $rating < 1200 => Difficulty::EASY,
                        $rating < 1800 => Difficulty::MEDIUM,
                        default => Difficulty::HARD,
                    };

                    return new ProblemDTO(
                        platform: Platform::CODEFORCES,
                        platformProblemId: $problemId,
                        name: $data['name'] ?? 'Problem ' . $index,
                        slug: 'problem-' . $problemId,
                        code: $index,
                        description: null,
                        difficulty: $difficulty,
                        rating: $rating,
                        points: null,
                        accuracy: null,
                        timeLimitMs: null,
                        memoryLimitMb: null,
                        totalSubmissions: 0,
                        acceptedSubmissions: 0,
                        solvedCount: 0,
                        tags: $data['tags'] ?? [],
                        topics: [],
                        url: "https://codeforces.com/problemset/problem/{$cfContestId}/{$index}",
                        editorialUrl: null,
                        contestId: (string) $cfContestId,
                        isPremium: false,
                        raw: $data
                    );
                })
                ->filter()
                ->take($limit)
                ->values();
        } catch (\Exception $e) {
            Log::error("Codeforces fetchProblems failed: {$e->getMessage()}");
            return collect();
        }
    }
}
