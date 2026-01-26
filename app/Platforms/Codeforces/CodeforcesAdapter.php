<?php

namespace App\Platforms\Codeforces;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CodeforcesAdapter implements PlatformAdapter
{
    public function __construct(
        protected CodeforcesClient $client
    ) {}

    public function platform(): string
    {
        return Platform::CODEFORCES->value;
    }

    public function profileUrl(string $handle): string
    {
        return "https://codeforces.com/profile/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchUserInfo($handle);

        return new ProfileDTO(
            platform: Platform::CODEFORCES,
            handle: $data['handle'],
            rating: $data['rating'] ?? null,
            totalSolved: 0, // not available from user.info
            raw: $data
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        $submissions = $this->client->fetchSubmissions($handle);

        return collect($submissions)
            ->filter(fn($sub) => ($sub['verdict'] ?? null) === 'OK')
            ->map(function ($sub) {
                $problem = $sub['problem'];

                $problemId = ($problem['contestId'] ?? '0') . $problem['index'];

                return new SubmissionDTO(
                    problemId: $problemId,
                    problemName: $problem['name'],
                    difficulty: $problem['rating'] ?? null,
                    verdict: Verdict::ACCEPTED,
                    submittedAt: CarbonImmutable::createFromTimestamp(
                        $sub['creationTimeSeconds']
                    )
                );
            });
    }
}
