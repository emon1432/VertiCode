<?php

namespace App\Platforms\Uva;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UvaClient
{
    private const UHUNT_API = 'https://uhunt.onlinejudge.org/api';
    private const UVA_URL = 'https://uva.onlinejudge.org';

    /**
     * Convert username to UVa ID
     */
    public function getUserId(string $handle): string
    {
        $response = Http::timeout(15)->get(self::UHUNT_API . '/uname2uid/' . urlencode($handle));

        if (! $response->ok()) {
            throw new \RuntimeException('UVa username lookup failed');
        }

        $userId = trim($response->body());

        if ($userId === '0' || empty($userId)) {
            throw new \RuntimeException('UVa user not found');
        }

        return $userId;
    }

    /**
     * Fetch user profile from UHunt
     */
    public function fetchProfile(string $handle): array
    {
        $userId = $this->getUserId($handle);

        try {
            $response = Http::timeout(15)->get(self::UHUNT_API . '/userstat/' . $userId);

            if (! $response->ok()) {
                return [
                    'handle' => $handle,
                    'total_solved' => 0,
                    'user_id' => $userId,
                ];
            }

            $data = $response->json();

            return [
                'handle' => $handle,
                'user_id' => $userId,
                'total_solved' => $data['solved'] ?? 0,
                'submissions' => $data['subm'] ?? 0,
                'rank' => $data['rank'] ?? null,
                'raw' => $data,
            ];
        } catch (\Exception $e) {
            Log::warning("UVa profile fetch failed for {$handle}: {$e->getMessage()}");
            return [
                'handle' => $handle,
                'total_solved' => 0,
                'user_id' => $userId,
            ];
        }
    }

    /**
     * Fetch all submissions for a user
     */
    public function fetchSubmissions(string $handle): array
    {
        try {
            $userId = $this->getUserId($handle);

            $response = Http::timeout(30)->get(self::UHUNT_API . '/subs-user/' . $userId);

            if (! $response->ok()) {
                throw new \RuntimeException('Failed to fetch UVa submissions');
            }

            $data = $response->json();
            return $data['subs'] ?? [];
        } catch (\Exception $e) {
            Log::error("UVa submissions fetch failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Fetch problem mapping (cached)
     */
    public function fetchProblems(): array
    {
        try {
            $response = Http::timeout(30)->get(self::UHUNT_API . '/p');

            if (! $response->ok()) {
                return [];
            }

            $problems = $response->json();
            $mapping = [];

            foreach ($problems as $problem) {
                $mapping[$problem[0]] = [
                    'name' => $problem[1],
                    'number' => $problem[0],
                ];
            }

            return $mapping;
        } catch (\Exception $e) {
            Log::warning("UVa problem mapping fetch failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Normalize submission verdict
     */
    public static function normalizeVerdict(int $statusCode): string
    {
        return match ($statusCode) {
            90 => 'AC',
            70 => 'WA',
            30 => 'CE',
            40 => 'RE',
            50 => 'TLE',
            60 => 'MLE',
            default => 'OTH',
        };
    }

    /**
     * Map language ID to name
     */
    public static function getLanguage(int $langId): string
    {
        return match ($langId) {
            1 => 'ANSI C',
            2 => 'Java',
            3 => 'C++',
            4 => 'Pascal',
            5 => 'C++11',
            6 => 'Python',
            default => 'Unknown',
        };
    }

    /**
     * Get problem URL
     */
    public static function getProblemUrl(int $problemId): string
    {
        return self::UVA_URL . '/index.php?option=com_onlinejudge&Itemid=8&page=show_problem&problem=' . $problemId;
    }
}
