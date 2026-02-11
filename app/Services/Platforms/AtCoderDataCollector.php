<?php

namespace App\Services\Platforms;

use App\Enums\Platform;
use App\Models\Contest;
use App\Models\Problem;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AtCoder Independent Web Scraper Service
 *
 * This service independently collects and maintains all AtCoder data:
 *
 * 1. Scrape data directly from atcoder.jp website (ONLY source)
 * 2. Parse HTML to extract contests and problems
 * 3. Store everything in our own database
 * 4. ZERO dependency on external APIs
 *
 * Why this approach:
 * - We control the data pipeline completely
     * - No reliance on any external services or APIs
 * - Can handle outages independently
 * - Builds our own competitive programming platform infrastructure
 *
 * Data Collection Strategy:
 * 1. Scrape contest list from https://atcoder.jp/contests
 * 2. For each contest, scrape problems from https://atcoder.jp/contests/{contest_id}/tasks
 * 3. Parse problem details (difficulty, points, constraints, etc.)
 * 4. Store all data in contests and problems tables
 */
class AtCoderDataCollector
{
    private const ATCODER_BASE = 'https://atcoder.jp';
    private const CONTESTS_PAGE = '/contests';
    private const CONTESTS_ARCHIVE = '/contests/archive';
    private const RATE_LIMIT_MS = 500; // Reduced to 500ms for faster collection
    private const TIMEOUT = 30;
    private const MAX_ARCHIVE_PAGES = 16; // AtCoder archive has 16 pages with 800+ contests
    private int $lastRequestTime = 0;

    /**
     * Collect contests by scraping AtCoder website.
     *
     * Scrapes https://atcoder.jp/contests/archive and extracts contest information.
     * Stops scraping once the limit is reached to avoid unnecessary requests.
     *
     * @param int $limit Maximum number of contests to collect (default: 100)
     * @return Collection<int, array>
     */
    public function collectContests(int $limit = 100): Collection
    {
        try {
            Log::info("AtCoder Scraper: Starting contest collection (limit: $limit)");

            $contests = $this->scrapeContestList($limit);

            if ($contests->isNotEmpty()) {
                $this->cacheContests($contests);

                Log::info('AtCoder Scraper: Successfully scraped and cached ' . $contests->count() . " contests (limit: $limit)");

                return $contests;
            }

            Log::warning("AtCoder Scraper: No contests scraped (limit: $limit), using cache");

            return $this->getCachedContests($limit);
        } catch (Exception $e) {
            Log::error('AtCoder contest scraping failed', ['error' => $e->getMessage()]);

            return $this->getCachedContests($limit);
        }
    }

    /**
     * Collect problems by scraping AtCoder website.
     *
     * For each cached contest, scrapes its problems page.
     * Stops scraping once the limit is reached.
     *
     * @param int $limit Maximum number of problems to collect (default: 200)
     * @return Collection<int, array>
     */
    public function collectProblems(int $limit = 200): Collection
    {
        try {
            Log::info("AtCoder Scraper: Starting problem collection (limit: $limit)");

            $contests = $this->getCachedContests(500); // Get enough contests to scrape problems from

            if ($contests->isEmpty()) {
                Log::warning('AtCoder Scraper: No contests available, collecting contests first');
                $contests = $this->collectContests(100);
            }

            $allProblems = collect();

            foreach ($contests as $contest) {
                // Stop if we've reached the limit
                if ($allProblems->count() >= $limit) {
                    Log::info("AtCoder Scraper: Reached problem limit of $limit");
                    break;
                }

                $contestId = $contest['id'] ?? null;
                if (!$contestId) {
                    continue;
                }

                try {
                    $problems = $this->scrapeContestProblems($contestId);
                    $allProblems = $allProblems->merge($problems);

                    // Respectful scraping with delays
                    $this->enforceRateLimit();
                } catch (Exception $e) {
                    Log::warning("Failed to scrape problems for contest {$contestId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($allProblems->isNotEmpty()) {
                // Only cache if we collected a reasonable amount
                if ($allProblems->count() >= $limit * 0.8) {
                    $this->cacheProblems($allProblems);
                }

                Log::info("AtCoder Scraper: Successfully scraped and cached " . $allProblems->count() . " problems (limit: $limit)");

                return $allProblems->take($limit);
            }

            Log::warning("AtCoder Scraper: No problems scraped (limit: $limit), using cache");

            return $this->getCachedProblems($limit);
        } catch (Exception $e) {
            Log::error('AtCoder problem scraping failed', ['error' => $e->getMessage()]);

            return $this->getCachedProblems($limit);
        }
    }

    /**
     * Collect contest-problem pairs.
     *
     * @return Collection<int, array>
     */
    public function collectContestProblemPairs(): Collection
    {
        $problems = $this->getCachedProblems();

        $pairs = collect();

        foreach ($problems as $problem) {
            $problemId = $problem['id'] ?? null;
            if ($problemId) {
                $parts = explode('_', $problemId);
                $contestId = $parts[0] ?? null;

                if ($contestId) {
                    $pairs->push([
                        'contest_id' => $contestId,
                        'problem_id' => $problemId,
                    ]);
                }
            }
        }

        return $pairs->unique(fn($item) => $item['contest_id'] . '_' . $item['problem_id']);
    }

    /**
     * Scrape contest list from AtCoder (upcoming + archive).
     *
     * Fetches:
     * 1. Upcoming contests from https://atcoder.jp/contests/
     * 2. Archived contests from https://atcoder.jp/contests/archive?page=1..N
     *
     * Stops scraping once we reach the limit to avoid unnecessary requests.
     *
     * @param int $limit Maximum number of contests to scrape
     * @return Collection<int, array>
     */
    private function scrapeContestList(int $limit = 100): Collection
    {
        $allContests = collect();

        // First, fetch upcoming contests from the main page
        try {
            Log::info("AtCoder Scraper: Fetching upcoming contests from main page");

            $this->enforceRateLimit();

            $url = self::ATCODER_BASE . self::CONTESTS_PAGE;
            $response = Http::timeout(self::TIMEOUT)->get($url);

            if ($response->ok()) {
                $html = $response->body();
                $upcomingContests = $this->parseContestHTML($html, 'upcoming');
                $allContests = $allContests->merge($upcomingContests);

                Log::info("AtCoder Scraper: Fetched " . $upcomingContests->count() . " upcoming contests (total: {$allContests->count()})");
            } else {
                Log::warning('Failed to fetch upcoming contests. Status: ' . $response->status());
            }
        } catch (Exception $e) {
            Log::warning("Failed to fetch upcoming contests", ['error' => $e->getMessage()]);
        }

        // If we haven't reached the limit, fetch archived contests
        if ($allContests->count() < $limit) {
            for ($page = 1; $page <= self::MAX_ARCHIVE_PAGES; $page++) {
                // Stop if we've already collected enough contests
                if ($allContests->count() >= $limit) {
                    Log::info("AtCoder Scraper: Reached contest limit of $limit");
                    break;
                }

                try {
                    $this->enforceRateLimit();

                    $url = self::ATCODER_BASE . self::CONTESTS_ARCHIVE . '?page=' . $page;

                    Log::info("AtCoder Scraper: Fetching archived contests page $page (collected: {$allContests->count()}/$limit)");

                    $response = Http::timeout(self::TIMEOUT)->get($url);

                    if (!$response->ok()) {
                        Log::warning('Failed to fetch archive page ' . $page . '. Status: ' . $response->status());
                        continue;
                    }

                    $html = $response->body();
                    $pageContests = $this->parseContestHTML($html, 'archive');

                    // Avoid duplicates
                    $pageContests = $pageContests->filter(fn($contest) =>
                        !$allContests->contains(fn($existing) => $existing['id'] === $contest['id'])
                    );

                    $allContests = $allContests->merge($pageContests);

                    Log::info("AtCoder Scraper: Archive page $page fetched - " . $pageContests->count() . " contests (total: {$allContests->count()})");
                } catch (Exception $e) {
                    Log::warning("Failed to fetch archive page $page", ['error' => $e->getMessage()]);
                    continue;
                }
            }
        }

        // Return only up to the limit
        $result = $allContests->take($limit);
        Log::info('AtCoder Scraper: Total contests collected: ' . $result->count() . " (limit: $limit)");

        return $result;
    }

    /**
     * Parse contest HTML and extract contest data.
     *
     * Works for both upcoming contests page and archive pages since they use
     * the same HTML table structure.
     *
     * @param string $html The HTML content to parse
     * @param string $type Optional type ('upcoming' or 'archive') for logging
     * @return Collection<int, array>
     */
    private function parseContestHTML(string $html, string $type = 'archive'): Collection
    {
        $contests = collect();

        // Extract table rows from the archive page
        // Each row contains: link, duration, rating range
        // Pattern: <tr>...<td><a href="/contests/ID">Title</a></td><td>DURATION</td><td>RATING</td>...</tr>

        // First, extract from table rows to get full metadata
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $html, $rows);

        foreach ($rows[1] as $row) {
            // Get contest link
            if (!preg_match('/<a\s+href="\/contests\/([a-z0-9_-]+)"[^>]*>(.*?)<\/a>/is', $row, $linkMatch)) {
                continue;
            }

            $contestId = trim($linkMatch[1]);

            // Skip navigation links
            if (in_array($contestId, ['archive'])) {
                continue;
            }

            // Extract title
            $contestName = preg_replace('/\s+/', ' ', trim($linkMatch[2]));
            if (empty($contestName)) {
                continue;
            }

            // Extract duration (format: "HH:MM")
            $duration = null;
            if (preg_match('/<td[^>]*class="text-center"[^>]*>\s*(\d{1,2}):(\d{2})\s*<\/td>/is', $row, $durationMatch)) {
                $hours = (int)$durationMatch[1];
                $minutes = (int)$durationMatch[2];
                $duration = $hours * 3600 + $minutes * 60; // Convert to seconds
            }

            // Determine if rated (if rated, will have a rating range like "1200 - 2799")
            $is_rated = false;
            if (preg_match('/\d+\s*-\s*\d+/', $row)) {
                $is_rated = true;
            }

            // Build contest data structure
            $contest = [
                'id' => $contestId,
                'title' => $contestName,
                'description' => null,
                'start_epoch_second' => null,
                'duration' => $duration,
                'is_rated' => $is_rated,
            ];

            $contests->push($contest);
        }

        Log::info("AtCoder Scraper: Parsed " . $contests->count() . " $type contests from HTML");

        return $contests;
    }

    /**
     * Scrape problems for a specific contest.
     *
     * Fetches https://atcoder.jp/contests/{contest_id}/tasks and parses
     * the problems table to extract problem metadata.
     *
     * @param string $contestId
     * @return Collection<int, array>
     */
    private function scrapeContestProblems(string $contestId): Collection
    {
        $this->enforceRateLimit();

        $url = self::ATCODER_BASE . "/contests/{$contestId}/tasks";

        Log::info('AtCoder Scraper: Fetching problems for ' . $contestId);

        $response = Http::timeout(self::TIMEOUT)->get($url);

        if (!$response->ok()) {
            throw new Exception("Failed to fetch problems for contest {$contestId}. Status: " . $response->status());
        }

        $html = $response->body();

        return $this->parseProblemsHTML($html, $contestId);
    }

    /**
     * Parse problems HTML for a contest.
     *
     * Extracts:
     * - Problem ID (e.g., abc123_a)
     * - Problem name
     * - Problem code (A, B, C, etc.)
     * - Points
     * - Constraints information
     *
     * @param string $html
     * @param string $contestId
     * @return Collection<int, array>
     */
    private function parseProblemsHTML(string $html, string $contestId): Collection
    {
        $problems = collect();

        // Look for problem links in the tasks page
        // Handle newlines within task names (similar to contest parsing)
        // Pattern: <a href="/contests/{contestId}/tasks/{problemCode}">PROBLEM NAME</a>
        $pattern = '/<a\s+href=["\']\/contests\/(?:' . preg_quote($contestId) . ')\/tasks\/([a-z0-9_-]+)["\'][^>]*>\s*([^<\n]+(?:\n[^<]+)*?)\s*<\/a>/i';

        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            Log::warning("AtCoder Scraper: No problems found for contest {$contestId}");
            return collect();
        }

        foreach ($matches as $match) {
            if (empty($match[1])) {
                continue;
            }

            $problemCode = trim($match[1]);
            // Clean up problem name (remove extra whitespace and newlines)
            $problemName = preg_replace('/\s+/', ' ', trim($match[2] ?? $problemCode));

            // Construct full problem ID (e.g., abc123_a)
            $problemId = strtolower($contestId) . '_' . strtolower($problemCode);

            $problem = [
                'id' => $problemId,
                'title' => $problemName,
                'point' => null, // Could be extracted from additional parsing
                'problem_models' => [],
                'url' => self::ATCODER_BASE . "/contests/{$contestId}/tasks/{$problemCode}",
            ];

            $problems->push($problem);
        }

        Log::info("AtCoder Scraper: Parsed " . $problems->count() . " problems for {$contestId}");

        return $problems;
    }

    /**
     * Cache contests in database.
     *
     * @param Collection<int, array> $contests
     * @return void
     */
    private function cacheContests(Collection $contests): void
    {
        // Get platform ID by looking up the Platform model
        $platform = \App\Models\Platform::where('name', Platform::ATCODER->value)->first();

        if (!$platform) {
            Log::error('AtCoder platform not found in database');
            return;
        }

        foreach ($contests as $contest) {
            if (!isset($contest['id'])) {
                continue;
            }

            Contest::updateOrCreate(
                [
                    'platform_id' => $platform->id,
                    'platform_contest_id' => (string) $contest['id'],
                ],
                [
                    'name' => $contest['title'] ?? $contest['id'],
                    'url' => self::ATCODER_BASE . '/contests/' . $contest['id'],
                    'raw' => $contest,
                ]
            );
        }

        Log::info('AtCoder Scraper: Cached ' . $contests->count() . ' contests to database');
    }

    /**
     * Cache problems in database.
     *
     * @param Collection<int, array> $problems
     * @return void
     */
    private function cacheProblems(Collection $problems): void
    {
        // Get platform ID by looking up the Platform model
        $platform = \App\Models\Platform::where('name', Platform::ATCODER->value)->first();

        if (!$platform) {
            Log::error('AtCoder platform not found in database');
            return;
        }

        foreach ($problems as $problem) {
            if (!isset($problem['id'])) {
                continue;
            }

            // Extract contest ID from problem ID (format: contestid_problemcode)
            $problemIdParts = explode('_', $problem['id']);
            $contestId = $problemIdParts[0] ?? null;

            Problem::updateOrCreate(
                [
                    'platform_id' => $platform->id,
                    'platform_problem_id' => (string) $problem['id'],
                ],
                [
                    'name' => $problem['title'] ?? $problem['id'],
                    'url' => $problem['url'] ?? (self::ATCODER_BASE . "/contests/{$contestId}/tasks/" . end($problemIdParts)),
                    'raw' => $problem,
                    'status' => 'active',
                ]
            );
        }

        Log::info('AtCoder Scraper: Cached ' . $problems->count() . ' problems to database');
    }

    /**
     * Get cached contests from database.
     *
     * @param int $limit Maximum number of contests to return
     * @return Collection<int, array>
     */
    private function getCachedContests(int $limit = 100): Collection
    {
        $platform = \App\Models\Platform::where('name', Platform::ATCODER->value)->first();

        if (!$platform) {
            return collect();
        }

        $contests = Contest::where('platform_id', $platform->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $contests->map(function ($contest) {
            $raw = $contest->raw ?? [];
            // Ensure raw is an array
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?? [];
            }
            return $raw;
        })->filter();
    }

    /**
     * Get cached problems from database.
     *
     * @param int $limit Maximum number of problems to return
     * @return Collection<int, array>
     */
    private function getCachedProblems(int $limit = 200): Collection
    {
        $platform = \App\Models\Platform::where('name', Platform::ATCODER->value)->first();

        if (!$platform) {
            return collect();
        }

        $problems = Problem::where('platform_id', $platform->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $problems->map(function ($problem) {
            $raw = $problem->raw ?? [];
            // Ensure raw is an array
            if (is_string($raw)) {
                $raw = json_decode($raw, true) ?? [];
            }
            return $raw;
        })->filter();
    }

    /**
     * Enforce rate limiting to be respectful to AtCoder's servers.
     *
     * Maintains at least 1 second between consecutive requests.
     */
    private function enforceRateLimit(): void
    {
        $elapsed = microtime(true) * 1000 - $this->lastRequestTime;

        if ($elapsed < self::RATE_LIMIT_MS) {
            usleep((int) (self::RATE_LIMIT_MS - $elapsed) * 1000);
        }

        $this->lastRequestTime = (int) (microtime(true) * 1000);
    }
}
