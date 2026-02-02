<?php

namespace App\Platforms\AtCoder;

use App\Support\Http\BaseHttpClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;

class AtCoderClient extends BaseHttpClient
{
    private const BASE_URL = 'https://atcoder.jp';
    private const API_URL = 'https://kenkoooo.com/atcoder/atcoder-api';
    private const CACHE_TTL = 3600; // 1 hour for problem mapping

    /**
     * Fetch user profile from AtCoder
     */
    public function fetchProfile(string $handle): array
    {
        $url = self::BASE_URL . '/users/' . urlencode($handle);

        $response = $this->get($url, [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ]);

        if (!$response->ok()) {
            throw new \RuntimeException('AtCoder user not found');
        }

        $crawler = new Crawler($response->body());

        // Verify handle matches
        $usernameElement = $crawler->filter('.username');
        if ($usernameElement->count() === 0 || $usernameElement->text() !== $handle) {
            throw new \RuntimeException('AtCoder user not found or handle mismatch');
        }

        $rating = null;
        $highestRating = null;
        $rank = null;
        $solved = 0;
        $rated = 0;

        $crawler->filter('table.dl-table tr')->each(function ($row) use (&$rating, &$highestRating, &$rank, &$solved, &$rated) {
            $label = trim($row->filter('th')->text(''));
            $value = trim($row->filter('td')->text(''));

            if ($label === 'Rating') {
                $rating = is_numeric($value) ? (int) $value : null;
            } elseif ($label === 'Highest Rating') {
                $highestRating = is_numeric($value) ? (int) $value : null;
            } elseif ($label === 'Rank') {
                $rank = $value;
            } elseif ($label === 'Accepted Count') {
                // Extract number before " / " if present
                if (preg_match('/^(\d+)/', $value, $matches)) {
                    $solved = (int) $matches[1];
                }
            } elseif ($label === 'Rated Matches Count') {
                $rated = is_numeric($value) ? (int) $value : 0;
            }
        });

        return [
            'handle' => $handle,
            'rating' => $rating,
            'highest_rating' => $highestRating,
            'rank' => $rank,
            'total_solved' => $solved,
            'rated_matches' => $rated,
        ];
    }

    /**
     * Fetch contest history and rating graph data
     */
    public function fetchContestHistory(string $handle): array
    {
        try {
            $url = self::BASE_URL . '/users/' . urlencode($handle) . '/history';

            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]);

            if (!$response->ok()) {
                Log::warning("Failed to fetch AtCoder contest history for {$handle}: " . $response->status());
                return [];
            }

            $crawler = new Crawler($response->body());
            $historyTable = $crawler->filter('table#history tbody');

            if ($historyTable->count() === 0) {
                return [];
            }

            $contests = [];
            $historyTable->filter('tr')->each(function (Crawler $row) use (&$contests) {
                try {
                    $cells = $row->filter('td');
                    if ($cells->count() < 6) {
                        return;
                    }

                    $timeText = $cells->eq(0)->text();
                    // Parse datetime and adjust for timezone (JST +9:00)
                    $timestamp = CarbonImmutable::parse($timeText, 'Asia/Tokyo')->setTimezone('UTC');

                    $contestLink = $cells->eq(1)->filter('a');
                    $contestName = $cells->eq(1)->text();
                    $contestUrl = null;
                    if ($contestLink->count() > 0) {
                        $href = $contestLink->attr('href');
                        $contestUrl = self::BASE_URL . substr($href, 1); // Remove leading /
                    }

                    $rank = $cells->eq(2)->text();
                    $performance = $cells->eq(3)->text();
                    $newRating = (int) $cells->eq(4)->text();

                    $ratingChangeText = $cells->eq(5)->text();
                    $ratingChange = 0;
                    if ($ratingChangeText !== '-' && is_numeric($ratingChangeText)) {
                        $ratingChange = (int) $ratingChangeText;
                    }

                    $contests[] = [
                        'timestamp' => $timestamp->toDateTimeString(),
                        'contest_name' => $contestName,
                        'contest_url' => $contestUrl,
                        'rank' => $rank,
                        'performance' => $performance,
                        'new_rating' => $newRating,
                        'rating_change' => $ratingChange,
                    ];
                } catch (\Exception $e) {
                    // Skip problematic rows
                }
            });

            return $contests;
        } catch (\Exception $e) {
            Log::warning("Failed to fetch AtCoder contest history for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch submissions from Kenkoooo API
     * Note: This API may have rate limits or access restrictions
     */
    public function fetchSubmissions(string $handle): array
    {
        try {
            $url = self::API_URL . '/results?user=' . urlencode($handle);

            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Origin' => 'https://kenkoooo.com',
                'Referer' => 'https://kenkoooo.com/',
            ]);

            if (!$response->ok()) {
                // Check if it's a 403 or rate limit
                if ($response->status() === 403) {
                    Log::warning("AtCoder Kenkoooo API returned 403 for {$handle}. API may be restricted or rate limited.");
                    throw new \RuntimeException('Kenkoooo API access forbidden (403). This may be due to rate limiting or API restrictions.');
                }
                throw new \RuntimeException('Failed to fetch AtCoder submissions: ' . $response->status());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error("AtCoder submissions fetch failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch problem mapping from Kenkoooo API (cached)
     * This is used to map problem IDs to problem names
     */
    public function fetchProblemMapping(): array
    {
        return Cache::remember('atcoder_problem_mapping', self::CACHE_TTL, function () {
            try {
                $url = self::API_URL . '/problems';

                $response = $this->get($url, [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Origin' => 'https://kenkoooo.com',
                    'Referer' => 'https://kenkoooo.com/',
                ]);

                if (!$response->ok()) {
                    Log::warning("Failed to fetch AtCoder problem mapping: " . $response->status());
                    return [];
                }

                $problems = $response->json();
                $mapping = [];

                foreach ($problems as $problem) {
                    $problemId = $problem['id'] ?? null;
                    $name = $problem['name'] ?? null;
                    $contestId = $problem['contest_id'] ?? null;

                    if ($problemId && $name) {
                        $mapping[$problemId] = [
                            'name' => $name,
                            'contest_id' => $contestId,
                        ];
                    }
                }

                return $mapping;
            } catch (\Exception $e) {
                Log::warning("Failed to fetch AtCoder problem mapping: {$e->getMessage()}");
                return [];
            }
        });
    }

    /**
     * Check if user profile exists
     */
    public function profileExists(string $handle): bool
    {
        try {
            $this->fetchProfile($handle);
            return true;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Get editorial link for a problem
     */
    public function getEditorialLink(string $problemLink): ?string
    {
        try {
            if (preg_match('/contests\/(.*)\/tasks/', $problemLink, $matches)) {
                $contestId = $matches[1];
                return "https://img.atcoder.jp/{$contestId}/editorial.pdf";
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return null;
    }

    /**
     * Normalize AtCoder verdict
     */
    public function normalizeVerdict(string $verdict): string
    {
        return match ($verdict) {
            'AC' => 'AC',
            'WA' => 'WA',
            'TLE' => 'TLE',
            'MLE' => 'MLE',
            'CE' => 'CE',
            'RE' => 'RE',
            default => 'OTH',
        };
    }

    /**
     * Get problem URL
     */
    public function getProblemUrl(string $contestId, string $problemId): string
    {
        return self::BASE_URL . '/contests/' . $contestId . '/tasks/' . $problemId;
    }

    /**
     * Get submission URL
     */
    public function getSubmissionUrl(string $contestId, int $submissionId): string
    {
        return self::BASE_URL . '/contests/' . $contestId . '/submissions/' . $submissionId;
    }
}
