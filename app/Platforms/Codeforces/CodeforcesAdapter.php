<?php

namespace App\Platforms\Codeforces;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
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
        throw new \RuntimeException('Not implemented');
    }
}
