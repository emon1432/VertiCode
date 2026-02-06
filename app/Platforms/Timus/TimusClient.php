<?php

namespace App\Platforms\Timus;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;

class TimusClient
{
    private const BASE_URL = 'http://acm.timus.ru';

    private const HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    /**
     * Fetch user profile from Timus
     * First gets user ID by handle, then fetches profile page and extracts stats
     */
    public function fetchProfile(string $handle): array
    {
        try {
            // First, get user ID by handle
            $profileUrl = self::BASE_URL . '/author.aspx?id=' . $handle;

            $profileResponse = Http::timeout(20)
                ->withHeaders(self::HEADERS)
                ->get($profileUrl);

            if (!$profileResponse->ok()) {
                throw new \RuntimeException('Failed to fetch author profile');
            }

            $profileHtml = $profileResponse->body();

            // Extract all stats from profile page
            $totalSolved = $this->extractTotalSolvedFromHtml($profileHtml);
            $rating = $this->extractRatingFromHtml($profileHtml);
            $name = $this->extractNameFromHtml($profileHtml);

            return [
                'handle' => $handle,
                'name' => $name,
                'user_id' => $handle,
                'total_solved' => $totalSolved,
                'rating' => $rating,
                'profile_url' => $profileUrl,
            ];
        } catch (\Exception $e) {
            Log::warning("Timus profile fetch failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Extract user name from profile HTML
     */
    private function extractNameFromHtml(string $html): ?string
    {
        try {
            $crawler = new Crawler($html);

            // Look for h2 with class author_name
            $nameElement = $crawler->filterXPath('//h2[@class="author_name"]')->first();

            if ($nameElement) {
                return trim($nameElement->text());
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Failed to extract name from HTML: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Try to extract total solved directly from profile HTML
     */
    private function extractTotalSolvedFromHtml(string $html): int
    {
        try {
            $crawler = new Crawler($html);

            // Look for table rows with "Problems solved"
            $rows = $crawler->filterXPath('//tr');

            foreach ($rows as $row) {
                $rowCrawler = new Crawler($row);
                $cells = $rowCrawler->filterXPath('.//td');

                if ($cells->count() >= 2) {
                    $label = trim($cells->eq(0)->text());
                    $value = trim($cells->eq(1)->text());

                    // Match exactly "Problems solved" - exclude rows with "Rank"
                    if (trim($label) === 'Problems solved') {
                        // Extract number from "1134 out of 1198"
                        if (preg_match('/^(\d+)\s+out\s+of/i', $value, $matches)) {
                            return (int) $matches[1];
                        }
                        // Fallback: just get first number
                        if (preg_match('/(\d+)/', $value, $matches)) {
                            return (int) $matches[1];
                        }
                    }
                }
            }

            return 0;
        } catch (\Exception $e) {
            Log::warning("Failed to extract total solved from HTML: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Extract rating from profile HTML
     */
    private function extractRatingFromHtml(string $html): ?int
    {
        try {
            $crawler = new Crawler($html);
            $rows = $crawler->filterXPath('//tr');

            foreach ($rows as $row) {
                $rowCrawler = new Crawler($row);
                $cells = $rowCrawler->filterXPath('.//td');

                if ($cells->count() >= 2) {
                    $label = trim($cells->eq(0)->text());
                    $value = trim($cells->eq(1)->text());

                    // Match exactly "Rating" - exclude "Rank by rating"
                    if (trim($label) === 'Rating') {
                        // Extract first number from "1720767 out of 1720767"
                        if (preg_match('/^(\d+)\s+out\s+of/i', $value, $matches)) {
                            return (int) $matches[1];
                        }
                        if (preg_match('/(\d+)/', $value, $matches)) {
                            return (int) $matches[1];
                        }
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Failed to extract rating from HTML: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Fetch all accepted submissions for a user
     */
    public function fetchSubmissions(string $handle): array
    {
        $submissions = [];
        $acmLink = self::BASE_URL;
        $count = 1000;
        $fromId = null;

        try {
            for ($i = 0; $i < 200; $i++) { // Increased iterations to get all submissions
                $initialUrl = $acmLink . '/status.aspx?author=' . urlencode($handle) . '&count=' . $count;

                if ($fromId !== null) {
                    $url = $initialUrl . '&from=' . $fromId;
                } else {
                    $url = $initialUrl;
                }

                $response = Http::timeout(20)
                    ->withHeaders(self::HEADERS)
                    ->retry(2, 1000)
                    ->get($url);

                if (!$response->ok()) {
                    Log::warning("Timus submissions fetch failed for {$handle} at from={$fromId}");
                    break;
                }

                $html = $response->body();

                // Check if user exists on first iteration
                if ($i === 0 && stripos($html, 'status_filter') === false) {
                    throw new \RuntimeException('Timus user not found');
                }

                $crawler = new Crawler($html);
                $table = $crawler->filterXPath('//table[@class="status"]')->first();

                if (!$table) {
                    break;
                }

                $rows = $table->filterXPath('.//tr')->slice(2); // Skip header rows
                $rowCount = 0;

                foreach ($rows as $tr) {
                    $row = new Crawler($tr);
                    $tds = $row->filterXPath('.//td');

                    if ($tds->count() < 6) {
                        continue;
                    }

                    $rowCount++;

                    // Extract submission ID
                    $submissionId = trim($tds->eq(0)->text());
                    $fromId = (int) $submissionId;

                    // Extract timestamp
                    $timeStr = trim($tds->eq(1)->text());
                    $timestamp = $this->parseTimusTimestamp($timeStr);

                    // Extract problem link and name
                    $problemLink = $tds->eq(3)->filterXPath('.//a')->attr('href') ?? '';
                    $problemName = trim($tds->eq(3)->text());

                    // Extract problem ID from link (e.g., "problem.aspx?space=1&num=1234&locale=en" -> 1234)
                    $problemId = null;
                    if (preg_match('/[&?]num=(\d+)/', $problemLink, $matches)) {
                        $problemId = $matches[1];
                    }

                    // Extract language
                    $language = trim($tds->eq(4)->text());

                    // Extract status
                    $status = trim($tds->eq(5)->text());
                    $verdict = $this->mapVerdict($status);

                    if ($verdict === 'AC') {
                        $submissions[] = [
                            'id' => $submissionId,
                            'problem_id' => $problemId,
                            'problem_name' => $problemName,
                            'problem_link' => self::BASE_URL . $problemLink . '&locale=en',
                            'timestamp' => $timestamp,
                            'language' => $language,
                            'status' => $verdict,
                        ];
                    }
                }

                // If we got fewer rows than requested count, we've reached the end
                if ($rowCount < $count) {
                    break;
                }

                // Decrement fromId for next page
                $fromId = max(1, $fromId - 1);
            }

            return $submissions;
        } catch (\Exception $e) {
            Log::warning("Timus submissions fetch error for {$handle}: {$e->getMessage()}");
            return $submissions;
        }
    }

    /**
     * Count total solved by iterating through all submissions
     * Tracks unique problems with at least one AC
     */
    private function countTotalSolvedFromSubmissions(string $handle): int
    {
        $acmLink = self::BASE_URL;
        $count = 1000;
        $fromId = null;
        $uniqueProblems = [];

        try {
            for ($i = 0; $i < 200; $i++) { // Increased iterations
                $initialUrl = $acmLink . '/status.aspx?author=' . urlencode($handle) . '&count=' . $count;

                if ($fromId !== null) {
                    $url = $initialUrl . '&from=' . $fromId;
                } else {
                    $url = $initialUrl;
                }

                $response = Http::timeout(20)
                    ->withHeaders(self::HEADERS)
                    ->retry(2, 1000)
                    ->get($url);

                if (!$response->ok()) {
                    Log::warning("Failed to count Timus total solved at from={$fromId}");
                    break;
                }

                $html = $response->body();

                $crawler = new Crawler($html);
                $table = $crawler->filterXPath('//table[@class="status"]')->first();

                if (!$table) {
                    break;
                }

                $rows = $table->filterXPath('.//tr')->slice(2);
                $rowCount = 0;

                foreach ($rows as $tr) {
                    $row = new Crawler($tr);
                    $tds = $row->filterXPath('.//td');

                    if ($tds->count() < 6) {
                        continue;
                    }

                    $rowCount++;

                    // Extract submission ID
                    $submissionId = trim($tds->eq(0)->text());
                    $fromId = (int) $submissionId;

                    // Extract status
                    $status = trim($tds->eq(5)->text());

                    if ($status === 'Accepted') {
                        // Extract problem ID from link
                        $problemLink = $tds->eq(3)->filterXPath('.//a')->attr('href') ?? '';
                        if (preg_match('/[&?]num=(\d+)/', $problemLink, $matches)) {
                            $uniqueProblems[$matches[1]] = true;
                        }
                    }
                }

                if ($rowCount < $count) {
                    Log::info("Timus total solved count for {$handle}: " . count($uniqueProblems) . " (iterated {$i} pages)");
                    break;
                }

                $fromId = max(1, $fromId - 1);
            }

            return count($uniqueProblems);
        } catch (\Exception $e) {
            Log::warning("Failed to count Timus total solved for {$handle}: {$e->getMessage()}");
            return 0;
        }
    }

    /**
     * Parse Timus timestamp format (HH:MM:SS DD Mon YYYY)
     */
    private function parseTimusTimestamp(string $timeStr): ?string
    {
        try {
            // Example: "14:37:22 26 Apr 2020"
            $dateObj = \DateTime::createFromFormat('H:i:s d M Y', $timeStr, new \DateTimeZone('UTC'));

            if (!$dateObj) {
                Log::warning("Failed to parse Timus timestamp: {$timeStr}");
                return null;
            }

            // Add 30 minutes offset as per StopStalk implementation
            $dateObj->modify('+30 minutes');

            return $dateObj->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning("Error parsing Timus timestamp '{$timeStr}': {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Map Timus verdict to our verdict format
     */
    private function mapVerdict(string $status): string
    {
        return match (true) {
            $status === 'Accepted' => 'AC',
            $status === 'Wrong answer' => 'WA',
            stripos($status, 'Runtime error') !== false => 'RE',
            $status === 'Memory limit exceeded' => 'MLE',
            $status === 'Time limit exceeded' => 'TLE',
            $status === 'Compilation error' => 'CE',
            default => 'OTH',
        };
    }
}
