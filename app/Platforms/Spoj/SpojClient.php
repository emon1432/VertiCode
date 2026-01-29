<?php

namespace App\Platforms\Spoj;

use Illuminate\Support\Facades\Http;

class SpojClient
{
    public function fetchProfile(string $handle): array
    {
        $url = "https://www.spoj.com/users/{$handle}/";

        $response = Http::withHeaders([
            // Browser-like headers (do NOT overdo)
            'User-Agent'      => 'Mozilla/5.0 (X11; Linux x86_64)',
            'Accept'          => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Referer'         => 'https://www.spoj.com/',
        ])
            ->timeout(20)
            ->get($url);

        /**
         * ⚠️ IMPORTANT:
         * SPOJ frequently blocks LOCAL / dev IPs with 403.
         * This is expected and OK.
         * Production servers usually succeed.
         */
        if ($response->status() === 403) {
            throw new \RuntimeException(
                'SPOJ blocked the request (403). This is expected on local machines and works on production servers.'
            );
        }

        if (! $response->ok()) {
            throw new \RuntimeException(
                'SPOJ request failed (HTTP ' . $response->status() . ')'
            );
        }

        $html = $response->body();

        // Explicit "user not found" cases
        if (
            stripos($html, 'User does not exist') !== false ||
            stripos($html, 'Page not found') !== false
        ) {
            throw new \RuntimeException('SPOJ user not found');
        }

        /**
         * Extract data using REGEX (most stable approach)
         * Matches real trackers + your Python reference logic
         */

        // Problems solved
        preg_match('/Problems\s+solved:\s*(\d+)/i', $html, $solvedMatch);
        $totalSolved = (int) ($solvedMatch[1] ?? 0);

        // Rank
        preg_match('/Rank:\s*(\d+)/i', $html, $rankMatch);
        $rank = isset($rankMatch[1]) ? (int) $rankMatch[1] : null;

        return [
            'total_solved' => $totalSolved,
            'rank' => $rank,
            'raw' => [
                'total_solved' => $totalSolved,
                'rank' => $rank,
            ],
        ];
    }
}
