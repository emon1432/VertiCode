<?php

namespace App\Services\LeetCode;

use App\Services\LeetCode\Clients\GraphQLClient;
use App\Services\LeetCode\DTO\LeetCodeProfileDTO;

class LeetCodeService
{
    public function __construct(
        protected GraphQLClient $client
    ) {}

    public function fetchProfile(string $username): LeetCodeProfileDTO
    {
        $data = $this->client->fetchUserProfile($username);

        $stats = collect(
            $data['submitStatsGlobal']['acSubmissionNum'] ?? []
        )->keyBy('difficulty');

        $easy = (int) ($stats['Easy']['count'] ?? 0);
        $medium = (int) ($stats['Medium']['count'] ?? 0);
        $hard = (int) ($stats['Hard']['count'] ?? 0);

        return new LeetCodeProfileDTO(
            username: $data['username'],
            totalSolved: $easy + $medium + $hard,
            easySolved: $easy,
            mediumSolved: $medium,
            hardSolved: $hard,
            ranking: $data['profile']['ranking'] ?? null,
            raw: $data
        );
    }
}
