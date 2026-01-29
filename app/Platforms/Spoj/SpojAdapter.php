<?php

namespace App\Platforms\Spoj;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

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
        // ❗ SPOJ submissions list is expensive to crawl
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::SPOJ,
            handle: $handle,
            rating: null,                    // SPOJ has no rating
            totalSolved: $data['total_solved'] ?? 0,
            raw: $data['raw'] ?? []
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect(); // not supported
    }
}
