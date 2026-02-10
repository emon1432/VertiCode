<?php

namespace App\Platforms\HackerRank;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class HackerRankAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
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
        $this->client->fetchProfile($handle);
        return "https://www.hackerrank.com/profile/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        // HackerRank exposes recent challenges via REST
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);
        $ratingGraph = $this->client->fetchRatingGraph($handle);

        return new ProfileDTO(
            platform: Platform::HACKERRANK,
            handle: $handle,
            rating: null, // no global rating
            totalSolved: (int) ($data['total_solved'] ?? 0),
            raw: [
                'badges' => $data['badges'] ?? null,
                'profile' => $data['raw'] ?? [],
                'rating_graph' => $ratingGraph,
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        $submissions = $this->client->fetchSubmissions($handle);

        return collect($submissions)->map(function ($row) {
            $problemUrl = "https://www.hackerrank.com" . ($row['url'] ?? '');
            $problemSlug = basename(parse_url($problemUrl, PHP_URL_PATH), '/');

            $submittedAt = isset($row['created_at'])
                ? CarbonImmutable::parse($row['created_at'])
                : now();

            return new SubmissionDTO(
                problemId: $problemSlug ?: ($row['name'] ?? 'unknown'),
                problemName: $row['name'] ?? $problemSlug ?: 'unknown',
                difficulty: null,
                verdict: Verdict::ACCEPTED,
                submittedAt: $submittedAt,
                raw: [
                    'problem_url' => $problemUrl,
                    'challenge_type' => $row['challenge_type'] ?? null,
                ]
            );
        });
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
