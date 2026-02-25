<?php

namespace App\Services\Platforms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CodeChefDataCollector
{
    private const BASE_URL = 'https://www.codechef.com';
    private const DELAY_MS = 500; // 500ms delay between requests
    private const TIMEOUT = 30;

    /**
     * Collect contests from CodeChef
     */
    public function collectContests(int $limit = 100): Collection
    {
        try {
            Log::info("CodeChef: Starting contest collection with limit: $limit");

            // Fetch all contests in one API call
            $allContestsData = $this->fetchAllContests();

            if ($allContestsData->isEmpty()) {
                Log::warning("CodeChef: No contests returned from API");
                return collect();
            }

            $filtered = $allContestsData->take($limit);

            Log::info("CodeChef: Collected {$filtered->count()} contests");
            return $filtered;
        } catch (\Exception $e) {
            Log::error("CodeChef contest collection failed: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Collect problems from CodeChef
     */
    public function collectProblems(int $limit = 200): Collection
    {
        try {
            Log::info("CodeChef: Starting problem collection with limit: $limit");

            $problems = $this->fetchProblemsFromCategories($limit);

            Log::info("CodeChef: Collected {$problems->count()} problems");
            return $problems;
        } catch (\Exception $e) {
            Log::error("CodeChef problem collection failed: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Fetch all contests from CodeChef API using pagination
     * The /api/list/contests/upcoming endpoint returns 20 contests per request
     * Use offset parameter to paginate through ~787 total contests
     */
    private function fetchAllContests(): Collection
    {
        try {
            $contests = collect();
            $pageSize = 20; // API always returns 20 results per request
            $offset = 0;
            $previousLastContest = null;

            Log::info("CodeChef: Starting contest collection with pagination");

            while (true) {
                usleep(self::DELAY_MS * 1000);

                $url = self::BASE_URL . "/api/list/contests/upcoming?offset=$offset";

                $response = Http::timeout(self::TIMEOUT)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'Accept' => 'application/json',
                    ])
                    ->get($url);

                if (!$response->ok()) {
                    Log::warning("Failed to fetch CodeChef contests at offset $offset: {$response->status()}");
                    break;
                }

                $data = $response->json();

                if (!isset($data['contests']) || empty($data['contests'])) {
                    Log::info("CodeChef: No more contests available at offset $offset");
                    break;
                }

                // Check if we're getting the same contests (indicates end of list)
                if (!empty($data['contests'])) {
                    $lastCode = end($data['contests'])['contest_code'] ?? null;
                    if ($lastCode === $previousLastContest) {
                        Log::info("CodeChef: Reached end of contests (same contests repeated)");
                        break;
                    }
                    $previousLastContest = $lastCode;
                }

                $batch = $this->mapContests($data['contests'], 'past'); // All contests from this endpoint
                $contests = $contests->merge($batch);
                $offset += $pageSize;

                Log::info("CodeChef: Fetched {$contests->count()} contests so far (offset: $offset)");
            }

            // Deduplicate by contest code
            $uniqueContests = $contests->unique('code');
            Log::info("CodeChef: Fetched {$uniqueContests->count()} unique contests total");

            return $uniqueContests;
        } catch (\Exception $e) {
            Log::error("Failed to fetch CodeChef contests: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Map contests from API response
     */
    private function mapContests(array $contests, string $type): Collection
    {
        return collect($contests)->map(function ($contest) use ($type) {
            $startTime = isset($contest['contest_start_date_iso'])
                ? strtotime($contest['contest_start_date_iso'])
                : null;

            $endTime = isset($contest['contest_end_date_iso'])
                ? strtotime($contest['contest_end_date_iso'])
                : null;

            $durationSeconds = isset($contest['contest_duration'])
                ? (int)$contest['contest_duration'] * 60
                : ($startTime && $endTime ? $endTime - $startTime : null);

            return [
                'id' => $contest['contest_code'],
                'name' => $contest['contest_name'],
                'code' => $contest['contest_code'],
                'type' => $type,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'durationSeconds' => $durationSeconds,
            ];
        });
    }

    /**
     * Fetch contests by type (upcoming, present, past) - DEPRECATED
     */
    private function fetchContestsByType(string $type): Collection
    {
        // This method is no longer used - keeping for backward compatibility
        return collect();
    }

    /**
     * Fetch problems from categories with pagination
     * CodeChef has 21,153+ problems available
     */
    private function fetchProblemsFromCategories(int $limit): Collection
    {
        try {
            $problems = collect();
            $batchSize = 500; // Fetch 500 problems per request
            $offset = 0;

            Log::info("CodeChef: Fetching up to $limit problems with pagination");

            while ($problems->count() < $limit) {
                usleep(self::DELAY_MS * 1000);

                $currentLimit = min($batchSize, $limit - $problems->count());

                // CodeChef API endpoint for problem list (all problems regardless of category)
                // Note: Removed sort to get diverse mix of practice and contest problems
                $url = self::BASE_URL . '/api/list/problems/school?'
                    . 'limit=' . $currentLimit
                    . '&offset=' . $offset;

                $response = Http::timeout(self::TIMEOUT)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'Accept' => 'application/json',
                    ])
                    ->get($url);

                if (!$response->ok()) {
                    Log::warning("Failed to fetch CodeChef problems at offset $offset: {$response->status()}");
                    break;
                }

                $data = $response->json();

                if (!isset($data['data']) || empty($data['data'])) {
                    Log::info("CodeChef: No more problems available at offset $offset");
                    break;
                }

                $batch = collect($data['data'])->map(function ($problem) {
                    return [
                        'id' => $problem['code'],
                        'code' => $problem['code'],
                        'name' => $problem['name'],
                        'contestCode' => $problem['contest_code'] ?? null,
                        'difficultyRating' => isset($problem['difficulty_rating']) && $problem['difficulty_rating'] !== '-1'
                            ? (int)$problem['difficulty_rating']
                            : null,
                        'successfulSubmissions' => (int)($problem['successful_submissions'] ?? 0),
                        'totalSubmissions' => (int)($problem['total_submissions'] ?? 0),
                        'distinctSuccessfulSubmissions' => (int)($problem['distinct_successful_submissions'] ?? 0),
                    ];
                });

                $problems = $problems->merge($batch);
                $offset += $currentLimit;

                Log::info("CodeChef: Fetched {$problems->count()} problems so far");

                // If we got fewer results than requested, we've reached the end
                if (count($data['data']) < $currentLimit) {
                    break;
                }
            }

            return $problems->take($limit);
        } catch (\Exception $e) {
            Log::error("Failed to fetch CodeChef problems: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Fetch specific contests by their codes (for missing contests referenced by problems)
     */
    public function collectContestsByCode(array $contestCodes): Collection
    {
        try {
            if (empty($contestCodes)) {
                return collect();
            }

            Log::info("CodeChef: Fetching " . count($contestCodes) . " contests by code");

            $contests = collect();
            foreach ($contestCodes as $code) {
                usleep(self::DELAY_MS * 1000);

                $url = self::BASE_URL . '/api/contests/' . $code;

                $response = Http::timeout(self::TIMEOUT)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        'Accept' => 'application/json',
                    ])
                    ->get($url);

                if (!$response->ok()) {
                    Log::warning("Failed to fetch contest $code: {$response->status()}");
                    continue;
                }

                $data = $response->json();
                if (!isset($data['data']) || empty($data['data'])) {
                    Log::warning("No data returned for contest $code");
                    continue;
                }

                $contest = $data['data'];
                $contests->push([
                    'id' => $contest['code'],
                    'code' => $contest['code'],
                    'name' => $contest['name'] ?? $contest['code'],
                    'startTime' => $contest['contest_start_date'] ?? null,
                    'endTime' => $contest['contest_end_date'] ?? null,
                    'phase' => $contest['status'] ?? 'UNKNOWN',
                    'type' => $contest['contest_type'] ?? 'UNKNOWN',
                    'url' => $contest['contest_url'] ?? null,
                ]);
            }

            Log::info("CodeChef: Fetched " . $contests->count() . " contests by code");
            return $contests;
        } catch (\Exception $e) {
            Log::error("Failed to fetch CodeChef contests by code: {$e->getMessage()}");
            return collect();
        }
    }
}
