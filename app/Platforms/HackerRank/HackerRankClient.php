<?php

namespace App\Platforms\HackerRank;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HackerRankClient
{
    private const BASE_URL = 'https://www.hackerrank.com';

    public function fetchProfile(string $handle): array
    {
        $url = self::BASE_URL . "/profile/{$handle}";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
            'Accept' => 'text/html',
            'Referer' => self::BASE_URL . '/',
        ])->timeout(25)->get($url);

        if ($response->status() === 404) {
            throw new \RuntimeException('HackerRank user not found');
        }

        if ($response->status() === 403) {
            throw new \RuntimeException(
                'HackerRank blocked request (403). Limited stats available.'
            );
        }

        if (! $response->ok()) {
            throw new \RuntimeException(
                'HackerRank request failed (HTTP ' . $response->status() . ')'
            );
        }

        $html = $response->body();

        preg_match('/__INITIAL_STATE__\s*=\s*({.*?});/s', $html, $matches);

        if (empty($matches[1])) {
            return [
                'total_solved' => null,
                'badges' => null,
                'raw' => [],
            ];
        }

        $json = json_decode($matches[1], true);

        if (! is_array($json)) {
            return [
                'total_solved' => null,
                'badges' => null,
                'raw' => [],
            ];
        }

        $profile = $json['profile'] ?? [];
        $totalSolved = $profile['solved_challenges'] ?? null;
        $badges = isset($profile['badges']) ? count($profile['badges']) : null;

        return [
            'total_solved' => is_numeric($totalSolved) ? (int) $totalSolved : null,
            'badges' => $badges,
            'raw' => $profile,
        ];
    }

    /**
     * Rating graph / contest history
     */
    public function fetchRatingGraph(string $handle): array
    {
        try {
            $url = self::BASE_URL . "/rest/hackers/{$handle}/rating_histories_elo";

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'Accept' => 'application/json',
                'Referer' => self::BASE_URL . '/',
            ])->timeout(20)->get($url);

            if (! $response->ok()) {
                return [];
            }

            $models = $response->json('models') ?? [];
            $graphs = [];

            foreach ($models as $contestClass) {
                $data = [];
                foreach ($contestClass['events'] ?? [] as $contest) {
                    $timestamp = $contest['date'] ?? null;
                    if ($timestamp) {
                        $timestamp = str_replace('Z', '', $timestamp);
                        $timestamp = str_replace('T', ' ', $timestamp);
                    }

                    $data[$timestamp] = [
                        'name' => $contest['contest_name'] ?? '',
                        'url' => self::BASE_URL . '/' . ($contest['contest_slug'] ?? ''),
                        'rating' => $contest['rating'] ?? null,
                        'rank' => $contest['rank'] ?? null,
                    ];
                }

                $graphs[] = [
                    'title' => 'HackerRank - ' . ($contestClass['category'] ?? 'Contest'),
                    'data' => $data,
                ];
            }

            return $graphs;
        } catch (\Exception $e) {
            Log::warning("HackerRank rating graph failed for {$handle}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch ALL submissions by paginating through recent_challenges
     * Keeps going until no more results (last_page = true)
     */
    public function fetchSubmissions(string $handle): array
    {
        $url = self::BASE_URL . "/rest/hackers/{$handle}/recent_challenges";
        $submissions = [];
        $cursor = 'null';
        $page = 0;
        $maxPages = 500; // safety limit

        while ($page < $maxPages) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                    'Accept' => 'application/json',
                    'Referer' => self::BASE_URL . '/',
                ])->timeout(20)->get($url, [
                    'limit' => 50, // increased from 10
                    'cursor' => $cursor,
                    'response_version' => 'v2',
                ]);

                if (! $response->ok()) {
                    Log::warning("HackerRank submissions page {$page} failed: HTTP {$response->status()}");
                    break;
                }

                $json = $response->json();
                $models = $json['models'] ?? [];

                if (empty($models)) {
                    break;
                }

                foreach ($models as $row) {
                    $submissions[] = $row;
                }

                // Check if we've reached the last page
                if ($json['last_page'] ?? false) {
                    break;
                }

                $cursor = $json['cursor'] ?? null;
                if (empty($cursor)) {
                    break;
                }

                $page++;
                usleep(300000); // 0.3s delay between pages

            } catch (\Exception $e) {
                Log::error("HackerRank submissions fetch error at page {$page}: {$e->getMessage()}");
                break;
            }
        }

        return $submissions;
    }

    /**
     * Problem details: tags, editorial link, author
     */
    public function fetchProblemDetails(string $problemLink): array
    {
        try {
            if (str_contains($problemLink, 'contests/')) {
                $restUrl = str_replace('contests/', 'rest/contests/', $problemLink);
            } else {
                $restUrl = str_replace(
                    'challenges/',
                    'rest/contests/master/challenges/',
                    $problemLink
                );
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'Accept' => 'application/json',
                'Referer' => self::BASE_URL . '/',
            ])->timeout(20)->get($restUrl);

            if (! $response->ok()) {
                return [];
            }

            $data = $response->json();
            $model = $data['model'] ?? [];

            $tags = [];
            if (! empty($model['track'])) {
                $tags[] = $model['track']['name'] ?? null;
            } elseif (! empty($model['primary_contest']['track'])) {
                $tags[] = $model['primary_contest']['track']['name'] ?? null;
            } elseif (! empty($model['primary_contest']['name'])) {
                $tags[] = $model['primary_contest']['name'] ?? null;
            }
            $tags = array_values(array_filter($tags));

            $editorial = ! empty($model['is_editorial_available'])
                ? rtrim($problemLink, '/') . '/editorial/'
                : null;

            $author = $model['author_name'] ?? null;

            return [
                'tags' => $tags,
                'editorial_link' => $editorial,
                'problem_author' => $author ? [$author] : null,
            ];
        } catch (\Exception $e) {
            Log::warning("HackerRank problem details failed: {$e->getMessage()}");
            return [];
        }
    }
}
