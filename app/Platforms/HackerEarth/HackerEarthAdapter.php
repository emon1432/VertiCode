<?php

namespace App\Platforms\HackerEarth;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use App\Enums\Platform;
use App\Enums\Verdict;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HackerEarthAdapter implements PlatformAdapter
{
    public function __construct(
        protected HackerEarthClient $client
    ) {}

    public function platform(): string
    {
        return Platform::HACKEREARTH->value;
    }

    public function profileUrl(string $handle): string
    {
        $this->client->fetchProfile($handle);
        return "https://www.hackerearth.com/@{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        // ⚠️ Currently has timeout issues, but keeping for future fix
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::HACKEREARTH,
            handle: $handle,
            rating: $data['rating'] ?? null,
            totalSolved: 0, // computed from submissions (currently 0 due to timeout)
            raw: $data
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        try {
            $submissions = $this->client->fetchSubmissions($handle);

            return collect($submissions)
                ->map(function ($sub) {
                    return new SubmissionDTO(
                        problemId: $sub['problem_id'],
                        problemName: $sub['problem_name'] ?: $sub['problem_id'],
                        difficulty: null,
                        verdict: $this->mapVerdict($sub['verdict'] ?? 'OTH'),
                        submittedAt: $sub['submitted_at']
                    );
                });
        } catch (\Exception $e) {
            Log::warning("HackerEarth submissions fetch failed for {$handle}: " . $e->getMessage());

            // Return empty collection to prevent sync failure
            return collect();
        }
    }

    private function mapVerdict(string $code): Verdict
    {
        return match ($code) {
            'AC' => Verdict::ACCEPTED,
            'WA' => Verdict::WRONG,
            'RE' => Verdict::RUNTIME,
            'TLE' => Verdict::TLE,
            default => Verdict::OTHER,
        };
    }
}
