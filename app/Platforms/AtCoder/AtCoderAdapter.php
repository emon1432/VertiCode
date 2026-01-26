<?php

namespace App\Platforms\AtCoder;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class AtCoderAdapter implements PlatformAdapter
{
    public function __construct(
        protected AtCoderClient $client
    ) {}

    public function platform(): string
    {
        return Platform::ATCODER->value;
    }

    public function profileUrl(string $handle): string
    {
        return "https://atcoder.jp/users/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::ATCODER,
            handle: $handle,
            rating: $data['rating'],
            totalSolved: $data['total_solved'],
            raw: $data
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect();
    }
}
