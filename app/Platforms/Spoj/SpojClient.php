<?php

namespace App\Platforms\Spoj;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\CarbonImmutable;

class SpojClient
{
    private const BASE_URL = 'https://www.spoj.com';

    /**
     * Fetch user profile from SPOJ
     * Handles Cloudflare challenge by retrying with longer timeout
     */
    public function fetchProfile(string $handle): array
    {
        if (! app()->environment('production')) {
            throw new \RuntimeException(
                'SPOJ sync is disabled outside production due to Cloudflare protection.'
            );
        }

        $url = self::BASE_URL . '/users/' . urlencode($handle) . '/';

        $html = $this->getWithCloudflareHandling($url);

        // Check for user not found
        if (
            stripos($html, 'User does not exist') !== false ||
            stripos($html, 'Page not found') !== false ||
            stripos($html, 'History of submissions') === false
        ) {
            throw new \RuntimeException('SPOJ user not found');
        }

        // Extract problems solved
        preg_match('/Problems\s+solved:\s*(\d+)/i', $html, $solvedMatch);
        $totalSolved = (int) ($solvedMatch[1] ?? 0);

        // Extract rank
        preg_match('/Rank:\s*(\d+)/i', $html, $rankMatch);
        $rank = isset($rankMatch[1]) ? (int) $rankMatch[1] : null;

        // Extract join date
        $joinDate = null;
        if (preg_match('/Member\s+since:\s*(\d{4}-\d{2}-\d{2})/i', $html, $joinMatch)) {
            $joinDate = $joinMatch[1];
        }

        // Extract problem list (for later use in submissions)
        $problemSlugs = $this->extractProblemSlugs($html, $handle);

        return [
            'handle' => $handle,
            'total_solved' => $totalSolved,
            'rank' => $rank,
            'join_date' => $joinDate,
            'problem_slugs' => $problemSlugs,
        ];
    }

    /**
     * Get content handling Cloudflare challenge
     * Retries with delays if Cloudflare challenge is detected
     */
    private function getWithCloudflareHandling(string $url, int $maxRetries = 3): string
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Cache-Control' => 'max-age=0',
        ];

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::withHeaders($headers)
                    ->timeout(60)   // increased from 30s
                    ->retry(2, 2000) // 2 retries, 2s backoff
                    ->get($url);

                if (!$response->ok()) {
                    if ($response->status() === 403) {
                        throw new \RuntimeException('SPOJ returned 403 Forbidden');
                    }
                    throw new \RuntimeException('SPOJ request failed (HTTP ' . $response->status() . ')');
                }

                $html = $response->body();

                // Check if we got Cloudflare challenge page
                if (
                    str_contains($html, 'Just a moment') ||
                    str_contains($html, 'Checking your browser') ||
                    str_contains($html, 'ray ID')
                ) {

                    if ($attempt < $maxRetries) {
                        // Cloudflare challenge detected, wait and retry
                        $waitTime = 5 + ($attempt * 2); // 7, 9, 11 seconds
                        Log::info("SPOJ Cloudflare challenge detected, waiting {$waitTime} seconds before retry {$attempt}/{$maxRetries}");
                        sleep($waitTime);
                        continue;
                    } else {
                        throw new \RuntimeException(
                            'SPOJ Cloudflare challenge could not be bypassed after ' . $maxRetries . ' attempts. ' .
                                'This typically works after a few retries. Try again in a moment.'
                        );
                    }
                }

                // Got valid content
                return $html;
            } catch (\Illuminate\Http\Client\RequestException $e) {
                if ($attempt >= $maxRetries) {
                    throw $e;
                }
                // Wait before retry
                sleep(3 + ($attempt * 2));
            }
        }

        throw new \RuntimeException('Failed to fetch SPOJ content after ' . $maxRetries . ' attempts');
    }

    /**
     * Extract problem slugs that user has submissions for
     */
    private function extractProblemSlugs(string $html, string $handle): array
    {
        try {
            $crawler = new Crawler($html);
            $problemSlugs = [];

            $crawler->filter('td a')->each(function (Crawler $node) use ($handle, &$problemSlugs) {
                $href = $node->attr('href');
                if ($href && preg_match('/\/status\/.*,' . preg_quote($handle, '/') . '\//', $href)) {
                    $text = trim($node->text());
                    if (!empty($text)) {
                        $problemSlugs[] = $text;
                    }
                }
            });

            return array_unique($problemSlugs);
        } catch (\Exception $e) {
            Log::warning("Failed to extract SPOJ problem slugs: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch submissions for a user (paginated)
     */
    public function fetchSubmissions(string $handle, int $limit = 500): array
    {
        try {
            $submissions = [];
            $start = 0;
            $perPage = 20;
            $prevId = -1;
            $maxPages = (int) ceil($limit / $perPage);

            for ($page = 0; $page < $maxPages; $page++) {
                $url = self::BASE_URL . '/status/' . urlencode($handle) . '/all/start=' . $start;
                $start += $perPage;

                try {
                    $html = $this->getWithCloudflareHandling($url, 2); // Fewer retries for submission pages
                } catch (\Exception $e) {
                    Log::warning("SPOJ submissions page {$page} failed for {$handle}: {$e->getMessage()}");
                    break;
                }

                $crawler = new Crawler($html);
                $tbody = $crawler->filter('tbody');

                if ($tbody->count() === 0 || $tbody->filter('tr')->count() <= 1) {
                    // No more submissions
                    break;
                }

                $pageSubmissions = [];
                $currentId = null;

                $tbody->filter('tr')->each(function (Crawler $row, $index) use (&$pageSubmissions, &$currentId, &$prevId, $handle) {
                    try {
                        $cells = $row->filter('td');
                        if ($cells->count() < 13) {
                            return;
                        }

                        // Submission ID
                        $subId = trim($cells->eq(1)->text());
                        if ($index === 0) {
                            $currentId = $subId;
                            if ($currentId === $prevId) {
                                // Duplicate page, stop
                                return;
                            }
                        }

                        // Time of submission
                        $timeText = trim($cells->eq(3)->filter('span')->first()->text());
                        $submittedAt = CarbonImmutable::parse($timeText);

                        // Problem
                        $problemLink = $cells->eq(5)->filter('a');
                        if ($problemLink->count() === 0) {
                            return;
                        }

                        $problemName = trim($problemLink->text());
                        $problemHref = $problemLink->attr('href');
                        $problemUrl = self::BASE_URL . $problemHref;

                        // Status
                        $statusHtml = $cells->eq(6)->html();
                        $status = $this->normalizeStatus($statusHtml);

                        // Language
                        $language = trim($cells->eq(12)->filter('span')->first()->text(''));

                        $pageSubmissions[] = [
                            'submission_id' => $subId,
                            'submitted_at' => $submittedAt,
                            'problem_name' => $problemName,
                            'problem_url' => $problemUrl,
                            'status' => $status,
                            'language' => $language,
                        ];
                    } catch (\Exception $e) {
                        // Skip problematic rows
                    }
                });

                if (!empty($pageSubmissions)) {
                    $submissions = array_merge($submissions, $pageSubmissions);
                    $prevId = $currentId;
                } else {
                    break;
                }

                // Small delay to avoid triggering Cloudflare
                usleep(1500000); // 1.5 second delay
            }

            return $submissions;
        } catch (\Exception $e) {
            Log::error("SPOJ fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch problem details (tags, author)
     */
    public function fetchProblemDetails(string $problemUrl): array
    {
        try {
            $html = $this->getWithCloudflareHandling($problemUrl, 2);
            $crawler = new Crawler($html);

            // Extract tags
            $tags = [];
            $crawler->filter('#problem-tags span')->each(function (Crawler $node) use (&$tags) {
                $tagText = trim($node->text());
                if (!empty($tagText) && str_starts_with($tagText, '#')) {
                    $tags[] = substr($tagText, 1); // Remove #
                }
            });

            // Extract problem author
            $author = null;
            $metaTable = $crawler->filter('table#problem-meta');
            if ($metaTable->count() > 0) {
                $authorLink = $metaTable->filter('a')->first();
                if ($authorLink->count() > 0) {
                    $authorHref = $authorLink->attr('href');
                    if (str_starts_with($authorHref, '/users/')) {
                        $author = str_replace('/users/', '', $authorHref);
                    }
                }
            }

            return [
                'tags' => $tags,
                'author' => $author,
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch SPOJ problem details: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Normalize SPOJ status
     */
    private function normalizeStatus(string $statusHtml): string
    {
        if (stripos($statusHtml, 'accepted') !== false) {
            return 'AC';
        } elseif (stripos($statusHtml, 'wrong') !== false) {
            return 'WA';
        } elseif (stripos($statusHtml, 'compilation') !== false) {
            return 'CE';
        } elseif (stripos($statusHtml, 'runtime') !== false) {
            return 'RE';
        } elseif (stripos($statusHtml, 'time limit') !== false) {
            return 'TLE';
        }
        return 'OTH';
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
}
