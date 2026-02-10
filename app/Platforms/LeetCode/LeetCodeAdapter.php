<?php

namespace App\Platforms\LeetCode;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\Contracts\Platforms\PlatformAdapter;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class LeetCodeAdapter implements PlatformAdapter, ContestSyncAdapter, ProblemSyncAdapter
{
    public function __construct(
        protected LeetCodeClient $client
    ) {}

    public function platform(): string
    {
        return Platform::LEETCODE->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchUserProfile($handle);
        return "https://leetcode.com/u/{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        // LeetCode doesn't provide full submission history via API
        // Only recent 20 AC submissions are available
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchUserProfile($handle);
        $difficultyStats = $this->client->extractDifficultyStats($profileData);

        // Fetch additional data
        $recentSubmissions = $this->client->fetchRecentSubmissions($handle);
        $contestInfo = $this->client->fetchContestInfo($handle);
        $calendar = $this->client->fetchSubmissionCalendar($handle);

        $totalSolved = $difficultyStats['easy'] + $difficultyStats['medium'] + $difficultyStats['hard'];

        // Build comprehensive raw data
        $rawData = [
            'username' => $profileData['username'],
            'profile' => $profileData['profile'] ?? [],
            'submitStatsGlobal' => $profileData['submitStatsGlobal'] ?? [],
            'easy_solved' => $difficultyStats['easy'],
            'medium_solved' => $difficultyStats['medium'],
            'hard_solved' => $difficultyStats['hard'],
            'ranking' => $profileData['profile']['ranking'] ?? null,
            'badges' => $profileData['badges'] ?? [],
            'upcoming_badges' => $profileData['upcomingBadges'] ?? [],
            'recent_submissions' => $recentSubmissions,
            'contest_ranking' => $contestInfo['userContestRanking'] ?? null,
            'contest_history' => $contestInfo['userContestRankingHistory'] ?? [],
            'calendar' => $calendar,
            'contest_rating' => $contestInfo['userContestRanking']['rating'] ?? null,
            'contest_global_ranking' => $contestInfo['userContestRanking']['globalRanking'] ?? null,
            'attended_contests_count' => $contestInfo['userContestRanking']['attendedContestsCount'] ?? 0,
        ];

        // Use contest rating if available, otherwise null
        $rating = $rawData['contest_rating'] ? (int) round($rawData['contest_rating']) : null;

        return new ProfileDTO(
            platform: Platform::LEETCODE,
            handle: $profileData['username'],
            rating: $rating,
            totalSolved: $totalSolved,
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        // LeetCode API doesn't provide comprehensive submission history
        // Only recent 20 AC submissions are available, which is too limited
        // for tracking unique problems solved over time
        return collect();
    }

    public function supportsContests(): bool
    {
        // LeetCode has contests but they don't provide a public API
        // They have weekly/biweekly contests but no easy way to fetch them
        return false;
    }

    public function fetchContests(int $limit = 100): Collection
    {
        // Not implemented - LeetCode doesn't provide contest API
        return collect();
    }

    public function supportsProblems(): bool
    {
        // LeetCode has problems but GraphQL API access is limited
        // We can try to implement basic problem fetching
        return false;
    }

    public function fetchProblems(int $limit = 500, ?string $contestId = null): Collection
    {
        // Not implemented - would require scraping or GraphQL with authentication
        return collect();
    }
}
