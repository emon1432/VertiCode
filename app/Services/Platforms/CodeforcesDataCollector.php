<?php

namespace App\Services\Platforms;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodeforcesDataCollector
{
    private const BASE_API = 'https://codeforces.com/api';
    private const DELAY_MS = 300; // 300ms delay between requests

    /**
     * Collect contests from Codeforces API
     */
    public function collectContests(int $limit = 100): Collection
    {
        try {
            Log::info("Codeforces: Starting contest collection with limit: $limit");

            $contests = $this->fetchContestList();

            if ($contests->isEmpty()) {
                Log::warning("Codeforces: No contests returned from API");
                return collect();
            }

            // Filter and limit to only finished/running contests
            $filtered = $contests
                ->filter(fn($contest) => in_array($contest['phase'] ?? '', ['FINISHED', 'RUNNING']))
                ->take($limit)
                ->map(function ($contest) {
                    return [
                        'id' => $contest['id'],
                        'name' => $contest['name'],
                        'type' => $contest['type'] ?? 'CF',
                        'phase' => $contest['phase'] ?? 'FINISHED',
                        'durationSeconds' => $contest['durationSeconds'] ?? 0,
                        'startTimeSeconds' => $contest['startTimeSeconds'] ?? null,
                        'relativeTimeSeconds' => $contest['relativeTimeSeconds'] ?? null,
                        'preparedBy' => $contest['preparedBy'] ?? null,
                        'contestantsSolved' => $contest['contestantsSolved'] ?? 0,
                    ];
                });

            Log::info("Codeforces: Collected {$filtered->count()} contests");
            return $filtered;
        } catch (\Exception $e) {
            Log::error("Codeforces contest collection failed: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Collect problems from Codeforces API
     */
    public function collectProblems(int $limit = 200): Collection
    {
        try {
            Log::info("Codeforces: Starting problem collection with limit: $limit");

            $problems = $this->fetchProblemset();

            if ($problems->isEmpty()) {
                Log::warning("Codeforces: No problems returned from API");
                return collect();
            }

            $filtered = $problems
                ->take($limit)
                ->map(function ($problem) {
                    return [
                        'contestId' => $problem['contestId'],
                        'index' => $problem['index'],
                        'id' => $problem['contestId'] . $problem['index'],
                        'name' => $problem['name'],
                        'type' => $problem['type'] ?? 'PROGRAMMING',
                        'rating' => $problem['rating'] ?? null,
                        'tags' => $problem['tags'] ?? [],
                        'solvedCount' => $problem['solvedCount'] ?? 0,
                    ];
                });

            Log::info("Codeforces: Collected {$filtered->count()} problems");
            return $filtered;
        } catch (\Exception $e) {
            Log::error("Codeforces problem collection failed: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Fetch contest list from Codeforces API
     */
    private function fetchContestList(): Collection
    {
        try {
            usleep(self::DELAY_MS * 1000);

            $response = Http::timeout(30)->get(
                self::BASE_API . '/contest.list?gym=false'
            );

            if (!$response->ok()) {
                throw new \RuntimeException("API request failed: {$response->status()}");
            }

            $data = $response->json();

            if (($data['status'] ?? null) !== 'OK') {
                throw new \RuntimeException($data['comment'] ?? 'API error');
            }

            return collect($data['result'] ?? []);
        } catch (\Exception $e) {
            Log::error("Failed to fetch Codeforces contest list: {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Fetch problemset from Codeforces API
     */
    private function fetchProblemset(): Collection
    {
        try {
            usleep(self::DELAY_MS * 1000);

            $response = Http::timeout(30)->get(
                self::BASE_API . '/problemset.problems'
            );

            if (!$response->ok()) {
                throw new \RuntimeException("API request failed: {$response->status()}");
            }

            $data = $response->json();

            if (($data['status'] ?? null) !== 'OK') {
                throw new \RuntimeException($data['comment'] ?? 'API error');
            }

            return collect($data['result']['problems'] ?? []);
        } catch (\Exception $e) {
            Log::error("Failed to fetch Codeforces problemset: {$e->getMessage()}");
            return collect();
        }
    }
}
