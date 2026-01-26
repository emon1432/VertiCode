<?php

namespace App\Platforms\CodeChef;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use Illuminate\Support\Collection;

class CodeChefAdapter implements PlatformAdapter
{
    public function __construct(
        protected CodeChefClient $client
    ) {}

    public function platform(): string
    {
        return Platform::CODECHEF->value;
    }

    public function profileUrl(string $handle): string
    {
        return "https://www.codechef.com/users/{$handle}";
    }

    public function supportsSubmissions(): bool
    {
        return false; // ❗ CodeChef submissions are expensive to crawl
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $data = $this->client->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::CODECHEF,
            handle: $handle,
            rating: $data['rating'] ?: null,
            totalSolved: $data['total_solved'] ?? 0,
            raw: $data['raw'] ?? []
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect(); // not supported
    }
}
