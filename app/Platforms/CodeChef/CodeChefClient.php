<?php

namespace App\Platforms\CodeChef;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CodeChefClient
{
    public function fetchProfile(string $handle): array
    {
        $url = "https://www.codechef.com/users/{$handle}";

        $response = Http::withHeaders([
            'User-Agent' => 'VertiCode/1.0',
        ])->get($url);

        if (! $response->ok()) {
            throw new \RuntimeException('CodeChef user not found');
        }

        $crawler = new Crawler($response->body());

        /* ---------------- Rating ---------------- */
        $rating = 0;
        if ($crawler->filter('.rating-number')->count()) {
            $rating = (int) trim($crawler->filter('.rating-number')->first()->text());
        }

        /* ---------------- Total Solved ---------------- */
        $totalSolved = 0;
        $solvedNodes = $crawler->filter('.rating-data-section.problems-solved h3');

        // JS uses eq(3) → 4th h3
        if ($solvedNodes->count() >= 4) {
            preg_match('/\d+/', $solvedNodes->eq(3)->text(), $m);
            $totalSolved = (int) ($m[0] ?? 0);
        }

        /* ---------------- Badges ---------------- */
        $badges = [];
        $crawler->filter('.widget.badges .badge .badge__title')->each(
            function (Crawler $node) use (&$badges) {
                $badges[] = trim($node->text());
            }
        );

        $contestBadge = $badges ? implode(', ', $badges) : null;

        return [
            'rating' => $rating,
            'total_solved' => $totalSolved,
            'contest_badge' => $contestBadge,
            'raw' => [
                'rating' => $rating,
                'total_solved' => $totalSolved,
                'contest_badge' => $contestBadge,
            ],
        ];
    }
}
