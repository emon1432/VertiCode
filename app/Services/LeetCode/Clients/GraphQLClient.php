<?php

namespace App\Services\LeetCode\Clients;

use Illuminate\Support\Facades\Http;

class GraphQLClient
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

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'User-Agent' => 'VertiCode/1.0',
        ])->post(self::ENDPOINT, [
            'query' => $query,
            'variables' => [
                'username' => $username,
            ],
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException('LeetCode request failed');
        }

        $json = $response->json();

        if (! empty($json['errors'])) {
            throw new \RuntimeException(
                $json['errors'][0]['message'] ?? 'LeetCode GraphQL error'
            );
        }

        if (empty($json['data']['matchedUser'])) {
            throw new \RuntimeException('LeetCode user not found');
        }

        return $json['data']['matchedUser'];
    }
}
