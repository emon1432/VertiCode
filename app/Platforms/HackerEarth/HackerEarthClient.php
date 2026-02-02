<?php

namespace App\Platforms\HackerEarth;

use Illuminate\Support\Facades\Http;

class HackerEarthClient
{
    protected string $baseUrl = 'https://www.hackerearth.com';

    public function profileExists(string $handle): bool
    {
        $url = "{$this->baseUrl}/@{$handle}/";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (VertiCode)',
        ])->get($url);

        // 404 = profile does not exist
        if ($response->status() === 404) {
            return false;
        }

        if (! $response->ok()) {
            throw new \RuntimeException('HackerEarth request failed');
        }

        // Basic sanity check (page title contains HackerEarth)
        return str_contains($response->body(), 'HackerEarth');
    }
}
