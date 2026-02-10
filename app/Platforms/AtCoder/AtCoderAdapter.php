<?php

namespace App\Platforms\AtCoder;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AtCoderAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
{
    public function __construct(
        protected AtCoderClient $client
    ) {}

    public function platform(): string
    {
        return Platform::ATCODER->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "https://atcoder.jp/users/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);
        $contestHistory = $this->client->fetchContestHistory($handle);

        // Build comprehensive raw data
        $rawData = [
            'handle' => $profileData['handle'],
            'rating' => $profileData['rating'],
            'highest_rating' => $profileData['highest_rating'],
            'rank' => $profileData['rank'],
            'rated_matches' => $profileData['rated_matches'],
            'contest_history' => $contestHistory,
            'rating_graph_data' => $this->buildRatingGraphData($contestHistory),
        ];

        return new ProfileDTO(
            platform: Platform::ATCODER,
            handle: $profileData['handle'],
            rating: $profileData['rating'],
            totalSolved: $profileData['total_solved'],
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);
            $problemMapping = $this->client->fetchProblemMapping();

            return collect($submissions)
                ->filter(fn($sub) => ($sub['result'] ?? null) === 'AC')
                ->map(function ($sub) use ($problemMapping) {
                    $problemId = $sub['problem_id'];
                    $contestId = $sub['contest_id'];
                    $submissionId = $sub['id'];

                    $problemInfo = $problemMapping[$problemId] ?? null;
                    $problemName = $problemInfo['name'] ?? $problemId;

                    $problemUrl = $this->client->getProblemUrl($contestId, $problemId);
                    $submissionUrl = $this->client->getSubmissionUrl($contestId, $submissionId);
                    $editorialLink = $this->client->getEditorialLink($problemUrl);

                    return new SubmissionDTO(
                        problemId: $problemId,
                        problemName: $problemName,
                        difficulty: null, // AtCoder doesn't provide difficulty ratings
                        verdict: Verdict::ACCEPTED,
                        submittedAt: CarbonImmutable::createFromTimestamp($sub['epoch_second']),
                        raw: [
                            'contest_id' => $contestId,
                            'submission_id' => $submissionId,
                            'language' => $sub['language'] ?? '',
                            'point' => $sub['point'] ?? 0,
                            'length' => $sub['length'] ?? 0,
                            'execution_time' => $sub['execution_time'] ?? null,
                            'problem_url' => $problemUrl,
                            'submission_url' => $submissionUrl,
                            'editorial_link' => $editorialLink,
                        ]
                    );
                });
        } catch (\Exception $e) {
            // ⚠️ Kenkoooo API may be rate limited or blocked
            // Log the error but return empty collection to allow profile sync to succeed
            if (str_contains($e->getMessage(), '403') || str_contains($e->getMessage(), 'forbidden')) {
                Log::warning("AtCoder Kenkoooo API is currently unavailable for {$handle}. Submissions will be skipped. Error: {$e->getMessage()}");
                return collect();
            }

            Log::error("AtCoder fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Build rating graph data from contest history
     */
    private function buildRatingGraphData(array $contestHistory): array
    {
        $graphData = [];

        foreach ($contestHistory as $contest) {
            $graphData[] = [
                'timestamp' => $contest['timestamp'],
                'contest_name' => $contest['contest_name'],
                'contest_url' => $contest['contest_url'],
                'rating' => $contest['new_rating'],
                'rating_change' => $contest['rating_change'],
                'rank' => $contest['rank'],
                'performance' => $contest['performance'],
            ];
        }

        // Reverse to get chronological order (scraping returns newest first)
        return array_reverse($graphData);
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
