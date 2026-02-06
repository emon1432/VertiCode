<?php

namespace App\Platforms\Codeforces;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CodeforcesAdapter implements PlatformAdapter
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
}
