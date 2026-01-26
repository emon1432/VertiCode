<?php

namespace App\Platforms\AtCoder;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AtCoderClient
{
    public function fetchProfile(string $handle): array
    {
        $response = Http::get("https://atcoder.jp/users/{$handle}");

        if (! $response->ok()) {
            throw new \RuntimeException('AtCoder user not found');
        }

        $crawler = new Crawler($response->body());

        $rating = null;
        $solved = 0;

        $crawler->filter('table.dl-table tr')->each(function ($row) use (&$rating, &$solved) {
            $label = trim($row->filter('th')->text(''));
            $value = trim($row->filter('td')->text(''));

            if ($label === 'Rating') {
                $rating = is_numeric($value) ? (int) $value : null;
            }

            if ($label === 'Accepted Count') {
                $solved = (int) $value;
            }
        });

        return [
            'rating' => $rating,
            'total_solved' => $solved,
        ];
    }
}
