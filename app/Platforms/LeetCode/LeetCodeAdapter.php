<?php

namespace App\Platforms\LeetCode;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use App\Enums\Platform;
use App\Services\LeetCode\LeetCodeService;
use Illuminate\Support\Collection;

class LeetCodeAdapter implements PlatformAdapter
{

    public function __construct(
        protected LeetCodeService $service
    ) {}


    public function platform(): string
    {
        return Platform::LEETCODE->value;
    }

    public function supportsSubmissions(): bool
    {
        return false;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        $profile = $this->service->fetchProfile($handle);

        return new ProfileDTO(
            platform: Platform::LEETCODE,
            handle: $profile->username,
            rating: null, // LeetCode has no stable global rating
            totalSolved: $profile->totalSolved,
            raw: $profile->raw
        );
    }

    public function fetchSubmissions(string $handle): Collection
    {
        return collect();
    }
}
