<?php

namespace App\Platforms\HackerEarth;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class HackerEarthClient
{
    protected string $baseUrl = 'https://www.hackerearth.com';
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36';

    public function profileExists(string $handle): bool
    {
        $url = "{$this->baseUrl}/@{$handle}/";

        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'text/html',
        ])->timeout(20)->get($url);

        if ($response->status() === 404) {
            return false;
        }

        if (! $response->ok()) {
            throw new \RuntimeException('HackerEarth request failed');
        }

        return str_contains($response->body(), 'HackerEarth');
    }

    public function fetchProfile(string $handle): array
    {
        if (! $this->profileExists($handle)) {
            throw new \RuntimeException('HackerEarth profile not found');
        }

        $ratingGraph = $this->fetchRatingGraph($handle);

        return [
            'handle' => $handle,
            'rating' => $this->extractLatestRating($ratingGraph),
            'rating_graph' => $ratingGraph,
        ];
    }

    public function fetchRatingGraph(string $handle): array
    {
        $url = "{$this->baseUrl}/ratings/AJAX/rating-graph/{$handle}";

        $response = Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'text/html',
            'Referer' => "{$this->baseUrl}/@{$handle}/",
        ])->timeout(20)->get($url);

        if (! $response->ok()) {
            return [];
        }

        $body = trim($response->body());
        if ($body === '') {
            return [];
        }

        if (! preg_match('/var dataset = (\[.*?\]);/s', $body, $matches)) {
            return [];
        }

        $data = json_decode($matches[1], true);
        if (! is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Fetch submissions from HackerEarth
     *
     * ⚠️ KNOWN ISSUE: This endpoint frequently times out
     * Possible solutions to try in the future:
     * 1. Use a proxy service
     * 2. Implement exponential backoff
     * 3. Parse the main submissions page HTML directly (not AJAX)
     * 4. Use browser automation (Selenium/Puppeteer)
     * 5. Contact HackerEarth for official API access
     *
     * Current status: Returns empty array on timeout
     */
    public function fetchSubmissions(string $handle, int $maxPages = 10): array
    {
        // Try with retry and longer timeout
        $initialUrl = "{$this->baseUrl}/submissions/{$handle}/";

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ])
            ->timeout(30) // Increased timeout
            ->retry(2, 1000) // Retry 2 times with 1 second delay
            ->get($initialUrl);

            if ($response->status() === 404) {
                throw new \RuntimeException('HackerEarth profile not found');
            }

            if (! $response->ok()) {
                throw new \RuntimeException('HackerEarth submissions request failed');
            }

            $headers = $this->buildAjaxHeaders($response, $initialUrl);

        } catch (\Exception $e) {
            // ⚠️ Timeout or connection error - log for future debugging
            \Log::warning("HackerEarth initial page failed for {$handle}: " . $e->getMessage());
            throw new \RuntimeException('HackerEarth submissions page unavailable: ' . $e->getMessage());
        }

        $submissions = [];
        $consecutiveFailures = 0;

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = "{$this->baseUrl}/AJAX/feed/newsfeed/submission/user/{$handle}/?page={$page}";

            try {
                $pageResponse = Http::withHeaders($headers)
                    ->timeout(30) // Increased timeout
                    ->retry(2, 1000) // Retry with delay
                    ->get($url);

                if (! $pageResponse->ok()) {
                    $consecutiveFailures++;
                    if ($consecutiveFailures >= 3) {
                        \Log::warning("HackerEarth: Too many failures for {$handle}, stopping at page {$page}");
                        break;
                    }
                    continue;
                }

                // Reset failure counter on success
                $consecutiveFailures = 0;

                $payload = $pageResponse->json();
                if (! is_array($payload)) {
                    break;
                }

                if (($payload['status'] ?? '') === 'ERROR') {
                    break;
                }

                $html = (string) ($payload['data'] ?? '');
                if (trim($html) === '') {
                    break;
                }

                $crawler = new Crawler($html);
                $rows = $crawler->filter('tbody tr');

                if ($rows->count() === 0) {
                    break;
                }

                $rows->each(function (Crawler $row) use (&$submissions) {
                    try {
                        $problemAnchor = $this->findProblemAnchor($row);
                        if (! $problemAnchor) {
                            return;
                        }

                        $href = $problemAnchor->getAttribute('href');
                        if (! $href) {
                            return;
                        }

                        $problemLink = str_starts_with($href, 'http')
                            ? $href
                            : "{$this->baseUrl}{$href}";

                        $problemName = trim($problemAnchor->textContent ?? '');

                        $statusText = $this->extractStatusText($row);
                        $verdict = $this->normalizeStatus($statusText);

                        $timestamp = $this->extractTimestamp($row);
                        if (! $timestamp) {
                            return;
                        }

                        $submittedAt = CarbonImmutable::createFromFormat(
                            'Y-m-d H:i:sP',
                            $timestamp,
                            'UTC'
                        );

                        if (! $submittedAt) {
                            return;
                        }

                        $submissionId = $this->extractSubmissionId($row);
                        $submissionUrl = $submissionId
                            ? "{$this->baseUrl}/submission/{$submissionId}"
                            : null;

                        $submissions[] = [
                            'problem_id' => $problemLink,
                            'problem_name' => $problemName,
                            'verdict' => $verdict,
                            'submitted_at' => $submittedAt,
                            'submission_url' => $submissionUrl,
                        ];
                    } catch (\Exception $e) {
                        // Skip problematic rows
                        \Log::debug("HackerEarth: Failed to parse row: " . $e->getMessage());
                    }
                });

                // Add small delay between pages to avoid rate limiting
                usleep(500000); // 0.5 second delay

            } catch (\Exception $e) {
                $consecutiveFailures++;
                \Log::warning("HackerEarth page {$page} failed for {$handle}: " . $e->getMessage());

                if ($consecutiveFailures >= 3) {
                    break;
                }

                // Continue to next page
                continue;
            }
        }

        return $submissions;
    }

    protected function buildAjaxHeaders(Response $response, string $referer): array
    {
        $setCookies = $response->headers()['Set-Cookie'] ?? [];
        $cookieHeader = $this->buildCookieHeader($setCookies);
        $csrfToken = $this->extractCsrfToken($setCookies);

        $headers = [
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'X-Requested-With' => 'XMLHttpRequest',
            'Connection' => 'keep-alive',
            'Referer' => $referer,
        ];

        if ($cookieHeader !== '') {
            $headers['Cookie'] = $cookieHeader;
        }

        if ($csrfToken) {
            $headers['X-CSRFToken'] = $csrfToken;
        }

        return $headers;
    }

    protected function extractCsrfToken(array $setCookies): ?string
    {
        $cookieString = implode('; ', $setCookies);

        if (preg_match('/csrfToken=([^;]+)/', $cookieString, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function buildCookieHeader(array $setCookies): string
    {
        return collect($setCookies)
            ->map(fn($cookie) => explode(';', $cookie)[0])
            ->filter()
            ->implode('; ');
    }

    protected function extractLatestRating(array $ratingGraph): ?int
    {
        if (! $ratingGraph) {
            return null;
        }

        $last = end($ratingGraph);
        if (! is_array($last)) {
            return null;
        }

        $rating = $last['rating'] ?? null;

        return is_numeric($rating) ? (int) $rating : null;
    }

    protected function extractTimestamp(Crawler $row): ?string
    {
        $html = $row->html() ?? '';

        if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+00:00/', $html, $matches)) {
            return $matches[0];
        }

        return null;
    }

    protected function extractStatusText(Crawler $row): string
    {
        if ($row->filter('td span[title]')->count()) {
            return (string) $row->filter('td span[title]')->first()->attr('title');
        }

        if ($row->filter('td[title]')->count()) {
            return (string) $row->filter('td[title]')->first()->attr('title');
        }

        if ($row->filter('td')->count() >= 3) {
            return trim($row->filter('td')->eq(2)->text());
        }

        return '';
    }

    protected function normalizeStatus(string $statusText): string
    {
        $statusText = strtolower($statusText);

        if (str_contains($statusText, 'accepted')) {
            return 'AC';
        }

        if (str_contains($statusText, 'partial')) {
            return 'PS';
        }

        if (str_contains($statusText, 'wrong')) {
            return 'WA';
        }

        if (str_contains($statusText, 'compilation')) {
            return 'CE';
        }

        if (str_contains($statusText, 'runtime')) {
            return 'RE';
        }

        if (str_contains($statusText, 'memory')) {
            return 'MLE';
        }

        if (str_contains($statusText, 'time')) {
            return 'TLE';
        }

        return 'OTH';
    }

    protected function extractSubmissionId(Crawler $row): ?string
    {
        $id = $row->attr('id');

        if (! $id) {
            return null;
        }

        if (preg_match('/(\d+)/', $id, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function findProblemAnchor(Crawler $row): ?\DOMElement
    {
        foreach ($row->filter('a') as $anchor) {
            $href = $anchor->getAttribute('href');

            if (! $href) {
                continue;
            }

            if ($this->looksLikeProblemLink($href)) {
                return $anchor;
            }
        }

        return $row->filter('a')->count()
            ? $row->filter('a')->first()->getNode(0)
            : null;
    }

    protected function looksLikeProblemLink(string $href): bool
    {
        return str_contains($href, '/practice/')
            || str_contains($href, '/problem/')
            || str_contains($href, '/challenge/')
            || str_contains($href, '/challenges/');
    }
}
