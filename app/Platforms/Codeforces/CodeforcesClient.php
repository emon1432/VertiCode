<?php

namespace App\Platforms\Codeforces;

use App\Support\Http\BaseHttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CodeforcesClient extends BaseHttpClient
{
    private const BASE_URL = 'https://codeforces.com/api';
    private const WEB_URL = 'https://codeforces.com';
    private const CACHE_TTL = 86400; // 24 hours for problem tags

    /**
     * Fetch user information including rating
     */
    public function fetchUserInfo(string $handle): array
    {
        $response = $this->get(
            self::BASE_URL . '/user.info?handles=' . urlencode($handle)
        )->json();

        if (($response['status'] ?? null) !== 'OK') {
            throw new \RuntimeException(
                $response['comment'] ?? 'Codeforces API error'
            );
        }

        return $response['result'][0];
    }

    /**
     * Fetch all submissions for a user
     */
    public function fetchSubmissions(string $handle, ?int $count = null): array
    {
        $url = self::BASE_URL . '/user.status?handle=' . urlencode($handle) . '&from=1';

        if ($count !== null) {
            $url .= '&count=' . $count;
        }

        $response = $this->get($url)->json();

        if (($response['status'] ?? null) !== 'OK') {
            throw new \RuntimeException(
                $response['comment'] ?? 'Codeforces submissions API error'
            );
        }

        return $response['result'];
    }

    /**
     * Fetch contest list from API
     */
    public function fetchContestList(): array
    {
        try {
            $response = $this->get(
                self::BASE_URL . '/contest.list?gym=false'
            )->json();

            if (($response['status'] ?? null) !== 'OK') {
                throw new \RuntimeException(
                    $response['comment'] ?? 'Codeforces contest.list API error'
                );
            }

            return $response['result'];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch Codeforces contest list: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch user contest participation history by scraping
     * This includes rating changes, rank, and problems solved per contest
     */
    public function fetchContestHistory(string $handle): array
    {
        try {
            $url = self::WEB_URL . '/contests/with/' . urlencode($handle);
            $response = $this->get($url)->body();

            $crawler = new Crawler($response);
            $contests = [];

            // Find the contest table
            $crawler->filter('table.tablesorter tr')->each(function (Crawler $row, $i) use (&$contests) {
                // Skip header row
                if ($i === 0) {
                    return;
                }

                $cells = $row->filter('td');
                if ($cells->count() < 5) {
                    return;
                }

                try {
                    $contestId = null;
                    $contestLink = $cells->eq(1)->filter('a')->first();
                    if ($contestLink->count() > 0) {
                        $href = $contestLink->attr('href');
                        if (preg_match('/\/contest\/(\d+)/', $href, $matches)) {
                            $contestId = (int)$matches[1];
                        }
                    }

                    $contestName = $cells->eq(1)->text();
                    $rank = (int)str_replace(',', '', trim($cells->eq(2)->text()));
                    $solvedCount = (int)trim($cells->eq(3)->text());

                    $ratingChangeText = trim($cells->eq(4)->text());
                    $ratingChange = 0;
                    if (preg_match('/([+-]\d+)/', $ratingChangeText, $matches)) {
                        $ratingChange = (int)$matches[1];
                    }

                    $newRating = null;
                    if (preg_match('/→\s*(\d+)/', $ratingChangeText, $matches)) {
                        $newRating = (int)$matches[1];
                    }

                    $contests[] = [
                        'contest_id' => $contestId,
                        'contest_name' => $contestName,
                        'rank' => $rank,
                        'solved_count' => $solvedCount,
                        'rating_change' => $ratingChange,
                        'new_rating' => $newRating,
                    ];
                } catch (\Exception $e) {
                    // Skip problematic rows
                }
            });

            return $contests;
        } catch (\Exception $e) {
            Log::warning("Failed to fetch Codeforces contest history for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch and cache problem tags from problemset.problems API
     * Returns a mapping of problem ID (contestId + index) => tags array
     */
    public function fetchProblemTags(): array
    {
        return Cache::remember('codeforces_problem_tags', self::CACHE_TTL, function () {
            try {
                $response = $this->get(
                    self::BASE_URL . '/problemset.problems'
                )->json();

                if (($response['status'] ?? null) !== 'OK') {
                    return [];
                }

                $tagMapping = [];
                foreach ($response['result']['problems'] as $problem) {
                    $contestId = $problem['contestId'] ?? null;
                    $index = $problem['index'] ?? null;

                    if ($contestId && $index) {
                        $problemId = $contestId . $index;
                        $tagMapping[$problemId] = [
                            'tags' => $problem['tags'] ?? [],
                            'rating' => $problem['rating'] ?? null,
                            'name' => $problem['name'] ?? '',
                        ];
                    }
                }

                return $tagMapping;
            } catch (\Exception $e) {
                Log::warning("Failed to fetch Codeforces problem tags: {$e->getMessage()}");
                return [];
            }
        });
    }

    /**
     * Calculate max rating from user info
     */
    public function calculateMaxRating(array $userInfo): ?int
    {
        return $userInfo['maxRating'] ?? $userInfo['rating'] ?? null;
    }

    /**
     * Check if user profile exists by attempting to fetch with minimal data
     */
    public function profileExists(string $handle): bool
    {
        try {
            // Use user.status with minimal count to check existence
            $response = $this->get(
                self::BASE_URL . '/user.status?handle=' . urlencode($handle) . '&from=1&count=2'
            )->json();

            return ($response['status'] ?? null) === 'OK';
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Normalize Codeforces verdict to standard format
     */
    public function normalizeVerdict(string $verdict): string
    {
        return match ($verdict) {
            'OK' => 'AC',
            'WRONG_ANSWER' => 'WA',
            'COMPILATION_ERROR' => 'CE',
            'RUNTIME_ERROR' => 'RE',
            'TIME_LIMIT_EXCEEDED' => 'TLE',
            'MEMORY_LIMIT_EXCEEDED' => 'MLE',
            'IDLENESS_LIMIT_EXCEEDED' => 'ILE',
            'SECURITY_VIOLATED' => 'SV',
            'CRASHED' => 'RE',
            'INPUT_PREPARATION_CRASHED' => 'RE',
            'CHALLENGED' => 'HCK',
            'SKIPPED' => 'SK',
            'TESTING' => 'TESTING',
            'REJECTED' => 'REJ',
            default => 'OTH',
        };
    }

    /**
     * Get submission view URL
     */
    public function getSubmissionUrl(int $contestId, int $submissionId): string
    {
        // Gym problems (contestId > 90000) don't have public submission links
        if ($contestId > 90000) {
            return '';
        }

        return self::WEB_URL . '/contest/' . $contestId . '/submission/' . $submissionId;
    }

    /**
     * Get problem URL
     */
    public function getProblemUrl(int $contestId, string $index): string
    {
        $arg = $contestId > 90000 ? 'gymProblem' : 'problem';
        return self::WEB_URL . '/problemset/' . $arg . '/' . $contestId . '/' . $index;
    }
}
