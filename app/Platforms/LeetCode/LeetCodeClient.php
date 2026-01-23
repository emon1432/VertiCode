<?php

namespace App\Platforms\LeetCode;

use App\Support\Http\BaseHttpClient;

class LeetCodeClient extends BaseHttpClient
{
    private const ENDPOINT = 'https://leetcode.com/graphql';

    public function fetchUserProfile(string $username): array
    {
        $query = <<<'GQL'
query userPublicProfile($username: String!) {
  matchedUser(username: $username) {
    username
    profile {
      ranking
      reputation
    }
    submitStatsGlobal {
      acSubmissionNum {
        difficulty
        count
      }
    }
  }
}
GQL;

        $payload = [
            'query' => $query,
            'variables' => [
                'username' => $username,
            ],
        ];

        $response = $this->post(
            self::ENDPOINT,
            $payload,
            [
                'Content-Type' => 'application/json',
                'User-Agent' => 'VertiCode/1.0',
            ]
        )->json();

        if (isset($response['errors'])) {
            throw new \RuntimeException(
                $response['errors'][0]['message'] ?? 'LeetCode GraphQL error'
            );
        }

        if (empty($response['data']['matchedUser'])) {
            throw new \RuntimeException('LeetCode user not found');
        }

        return $response['data']['matchedUser'];
    }
}
