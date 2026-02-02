<?php

namespace App\Platforms\Spoj;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SpojAdapter implements PlatformAdapter
{
    public function __construct(
        protected SpojClient $client
    ) {}

    public function platform(): string
    {
        return Platform::SPOJ->value;
    }

    public function profileUrl(string $handle): string
    {
        return "https://www.spoj.com/users/{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profileData = $this->client->fetchProfile($handle);

        // Build raw data
        $rawData = [
            'handle' => $profileData['handle'],
            'rank' => $profileData['rank'],
            'join_date' => $profileData['join_date'],
            'problem_slugs_count' => count($profileData['problem_slugs'] ?? []),
        ];

        return new ProfileDTO(
            platform: Platform::SPOJ,
            handle: $profileData['handle'],
            rating: null, // SPOJ has no rating system
            totalSolved: $profileData['total_solved'],
            raw: $rawData
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);

            return collect($submissions)
                ->filter(fn($sub) => $sub['status'] === 'AC')
                ->map(function ($sub) {
                    // Extract problem slug from URL
                    $problemSlug = basename(parse_url($sub['problem_url'], PHP_URL_PATH), '/');

                    return new SubmissionDTO(
                        problemId: $problemSlug,
                        problemName: $sub['problem_name'],
                        difficulty: null, // SPOJ doesn't have difficulty ratings
                        verdict: Verdict::ACCEPTED,
                        submittedAt: $sub['submitted_at'],
                        raw: [
                            'submission_id' => $sub['submission_id'],
                            'problem_url' => $sub['problem_url'],
                            'language' => $sub['language'],
                        ]
                    );
                });
        } catch (\Exception $e) {
            // If Cloudflare challenge fails after retries, use profile total_solved
            if (str_contains($e->getMessage(), 'Cloudflare challenge')) {
                Log::warning("SPOJ Cloudflare challenge failed for {$handle}. Using profile total_solved. Error: {$e->getMessage()}");
                return collect();
            }

            Log::error("SPOJ fetchSubmissions failed for {$handle}: {$e->getMessage()}");
            throw $e;
        }
    }
}
