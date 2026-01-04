<?php

namespace App\Platforms\Codeforces;

use App\Support\Http\BaseHttpClient;

class CodeforcesClient extends BaseHttpClient
{
    private const BASE_URL = 'https://codeforces.com/api';

    public function fetchUserInfo(string $handle): array
    {
        $response = $this->get(
            self::BASE_URL . '/user.info?handles=' . urlencode($handle)
        )->json();

        if (($response['status'] ?? null) !== 'OK') {
            throw new \RuntimeException(
                $response['comment'] ?? 'Codeforces API error'
            );
        }

        return $response['result'][0];
    }

    public function fetchSubmissions(string $handle): array
    {
        $response = $this->get(
            self::BASE_URL . '/user.status?handle=' . urlencode($handle)
        )->json();

        if (($response['status'] ?? null) !== 'OK') {
            throw new \RuntimeException(
                $response['comment'] ?? 'Codeforces submissions API error'
            );
        }

        return $response['result'];
    }
}
