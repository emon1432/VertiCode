<?php

namespace App\Contracts\Platforms;

use Illuminate\Support\Collection;

interface ContestSyncAdapter
{
    /**
     * Fetch contests from the platform.
     *
     * @param int $limit Maximum number of contests to fetch
     * @return Collection<\App\DataTransferObjects\Platform\ContestDTO>
     */
    public function fetchContests(int $limit = 100): Collection;

    /**
     * Check if the platform supports contest syncing.
     */
    public function supportsContests(): bool;
}
