<?php

namespace App\Platforms\HackerRank;

use Illuminate\Support\Facades\Http;

class HackerRankClient
{
    public function fetchProfile(string $handle): array
    {
        $url = "https://www.hackerrank.com/profile/{$handle}";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
            'Accept' => 'text/html',
            'Referer' => 'https://www.hackerrank.com/',
        ])
            ->timeout(20)
            ->get($url);

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

        // Attempt to extract embedded JSON
        preg_match('/__INITIAL_STATE__\s*=\s*({.*?});/s', $html, $matches);

        if (empty($matches[1])) {
            // Fallback: profile exists but data not accessible
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

        // Defensive extraction (structure changes often)
        $profile = $json['profile'] ?? [];

        $totalSolved = $profile['solved_challenges'] ?? null;
        $badges = isset($profile['badges']) ? count($profile['badges']) : null;

        return [
            'total_solved' => is_numeric($totalSolved) ? (int) $totalSolved : null,
            'badges' => $badges,
            'raw' => $profile,
        ];
    }
}
