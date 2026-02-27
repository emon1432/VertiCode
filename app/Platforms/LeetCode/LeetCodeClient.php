<?php

namespace App\Platforms\LeetCode;

use App\Support\Http\BaseHttpClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class LeetCodeClient extends BaseHttpClient
{
    private const GRAPHQL_ENDPOINT = 'https://leetcode.com/graphql';
    private const RETRIABLE_STATUSES = [403, 429, 499, 503, 504];

    /**
     * Execute GraphQL request with retries for transient Cloudflare/rate-limit blocks.
     */
    private function postGraphQL(array $payload): array
    {
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Content-Type' => 'application/json',
            'Origin' => 'https://leetcode.com',
            'Referer' => 'https://leetcode.com/',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        ];

        $lastException = null;

        foreach ([self::GRAPHQL_ENDPOINT, self::GRAPHQL_ENDPOINT . '/'] as $endpoint) {
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $response = $this->post($endpoint, $payload, $headers)->json();

                    if (is_array($response)) {
                        return $response;
                    }

                    throw new \RuntimeException('LeetCode returned invalid JSON response');
                } catch (RequestException $e) {
                    $status = $e->response?->status();
                    $lastException = $e;

                    if (in_array($status, self::RETRIABLE_STATUSES, true) && $attempt < 3) {
                        usleep((int) ((400 * $attempt + random_int(100, 300)) * 1000));
                        continue;
                    }

                    if (! in_array($status, self::RETRIABLE_STATUSES, true)) {
                        throw $e;
                    }
                } catch (\Throwable $e) {
                    $lastException = $e;

                    if ($attempt < 3) {
                        usleep((int) ((400 * $attempt + random_int(100, 300)) * 1000));
                        continue;
                    }
                }
            }
        }

        throw new \RuntimeException(
            'LeetCode GraphQL request blocked or unavailable. Please retry after a short delay.',
            0,
            $lastException
        );
    }

    /**
     * Fetch user profile with stats and badges
     */
    public function fetchUserProfile(string $username): array
    {
        $query = <<<'GQL'
query userPublicProfile($username: String!) {
  matchedUser(username: $username) {
    username
    profile {
      ranking
      userAvatar
      realName
      aboutMe
      countryName
      skillTags
      starRating
    }
    submitStatsGlobal {
      acSubmissionNum {
        difficulty
        count
      }
    }
    badges {
      id
      name
      shortName
      displayName
      icon
      creationDate
    }
    upcomingBadges {
      name
      icon
      progress
    }
  }
}
GQL;

        $response = $this->postGraphQL([
            'query' => $query,
            'variables' => [
                'username' => $username,
            ],
        ]);

        if (! empty($response['errors'])) {
            throw new \RuntimeException(
                $response['errors'][0]['message'] ?? 'LeetCode GraphQL error'
            );
        }

        if (empty($response['data']['matchedUser'])) {
            throw new \RuntimeException('LeetCode user not found');
        }

        return $response['data']['matchedUser'];
    }

    /**
     * Fetch recent AC submissions (last 20)
     */
    public function fetchRecentSubmissions(string $username): array
    {
        try {
            $query = <<<'GQL'
query recentAcSubmissions($username: String!, $limit: Int!) {
  recentAcSubmissionList(username: $username, limit: $limit) {
    id
    title
    titleSlug
    timestamp
  }
}
GQL;

            $response = $this->postGraphQL([
                'query' => $query,
                'variables' => [
                    'username' => $username,
                    'limit' => 20,
                ],
            ]);

            if (! empty($response['errors'])) {
                Log::warning("LeetCode recent submissions GraphQL error for {$username}: " . ($response['errors'][0]['message'] ?? 'unknown'));
                return [];
            }

            return $response['data']['recentAcSubmissionList'] ?? [];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch LeetCode recent submissions for {$username}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch user contest info and rating
     */
    public function fetchContestInfo(string $username): array
    {
        try {
            $query = <<<'GQL'
query userContestRankingInfo($username: String!) {
  userContestRanking(username: $username) {
    attendedContestsCount
    rating
    globalRanking
    totalParticipants
    topPercentage
  }
  userContestRankingHistory(username: $username) {
    attended
    rating
    ranking
    trendDirection
    problemsSolved
    totalProblems
    finishTimeInSeconds
    contest {
      title
      startTime
    }
  }
}
GQL;

            $response = $this->postGraphQL([
                'query' => $query,
                'variables' => [
                    'username' => $username,
                ],
            ]);

            if (! empty($response['errors'])) {
                Log::warning("LeetCode contest info GraphQL error for {$username}: " . ($response['errors'][0]['message'] ?? 'unknown'));
                return [];
            }

            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch LeetCode contest info for {$username}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch submission statistics by year
     */
    public function fetchSubmissionCalendar(string $username): array
    {
        try {
            $query = <<<'GQL'
query userProfileCalendar($username: String!) {
  matchedUser(username: $username) {
    userCalendar {
      activeYears
      streak
      totalActiveDays
      submissionCalendar
    }
  }
}
GQL;

            $response = $this->postGraphQL([
                'query' => $query,
                'variables' => [
                    'username' => $username,
                ],
            ]);

            if (! empty($response['errors']) || empty($response['data']['matchedUser'])) {
                return [];
            }

            return $response['data']['matchedUser']['userCalendar'] ?? [];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch LeetCode submission calendar for {$username}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Check if user profile exists
     */
    public function profileExists(string $username): bool
    {
        try {
            $this->fetchUserProfile($username);
            return true;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'not found')) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * Extract difficulty stats from profile data
     */
    public function extractDifficultyStats(array $profileData): array
    {
        $stats = collect(
            $profileData['submitStatsGlobal']['acSubmissionNum'] ?? []
        )->keyBy('difficulty');

        return [
            'easy' => (int) ($stats['Easy']['count'] ?? 0),
            'medium' => (int) ($stats['Medium']['count'] ?? 0),
            'hard' => (int) ($stats['Hard']['count'] ?? 0),
        ];
    }
}
