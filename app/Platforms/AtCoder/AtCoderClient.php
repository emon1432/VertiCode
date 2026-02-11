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
    private const CACHE_TTL = 3600; // 1 hour for problem mapping

    /**
     * Fetch submissions from AtCoder directly
     *
     * Scrapes user submission page to get submission data.
     * Independently collected from atcoder.jp - no external APIs used.
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
     * Fetch submissions from AtCoder directly
     *
     * Scrapes user submission page to get submission data.
     * Independently collected from atcoder.jp - no external APIs used.
     */
    public function fetchSubmissions(string $handle): array
    {
        try {
            $url = self::BASE_URL . '/users/' . urlencode($handle) . '/submissions';

            $response = $this->get($url, [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]);

            if (!$response->ok()) {
                Log::warning("Failed to fetch AtCoder submissions for {$handle}: " . $response->status());
                return [];
            }

            $crawler = new Crawler($response->body());
            $submissions = [];

            // Parse submission table from AtCoder directly
            $crawler->filter('table#submissions-table tbody tr')->each(function (Crawler $row) use (&$submissions) {
                try {
                    $cells = $row->filter('td');
                    if ($cells->count() < 4) {
                        return;
                    }

                    $submissions[] = [
                        'timestamp' => trim($cells->eq(0)->text()),
                        'problem_id' => trim($cells->eq(1)->text()),
                        'verdict' => trim($cells->eq(3)->text()),
                        'exec_time' => trim($cells->eq(4)->text() ?? 'N/A'),
                        'memory' => trim($cells->eq(5)->text() ?? 'N/A'),
                    ];
                } catch (\Exception $e) {
                    // Skip problematic rows
                }
            });

            return $submissions;
        } catch (\Exception $e) {
            Log::error("AtCoder submissions fetch failed for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch problem mapping from our own database cache
     * Uses independently scraped data from our AtCoderDataCollector
     *
     * NO external API dependency - all data is from atcoder.jp directly
     */
    public function fetchProblemMapping(): array
    {
        return Cache::remember('atcoder_problem_mapping', self::CACHE_TTL, function () {
            try {
                // Get problems from our database (populated by AtCoderDataCollector)
                $problems = \App\Models\Problem::where('platform_id', '!=', null)
                    ->whereHas('contest', function ($query) {
                        $query->where('platform_id', '!=', null);
                    })
                    ->get();

                $mapping = [];

                foreach ($problems as $problem) {
                    $problemId = $problem->platform_problem_id;
                    $name = $problem->name;
                    $contestId = $problem->contest_id;

                    if ($problemId && $name) {
                        $mapping[$problemId] = [
                            'name' => $name,
                            'contest_id' => $contestId,
                        ];
                    }
                }

                return $mapping;
            } catch (\Exception $e) {
                Log::warning("Failed to fetch AtCoder problem mapping from database: {$e->getMessage()}");
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
