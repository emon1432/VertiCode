<?php

namespace App\Platforms\HackerEarth;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

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
        return "https://www.hackerearth.com/@{$handle}/";
    }

    public function supportsSubmissions(): bool
    {
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        if (! $this->client->profileExists($handle)) {
            throw new \RuntimeException('HackerEarth user not found');
        }

        return new ProfileDTO(
            platform: Platform::HACKEREARTH,
            handle: $handle,
            rating: null,
            totalSolved: 0,
            raw: [
                'note' => 'HackerEarth does not expose public competitive stats',
            ]
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect();
    }
}
