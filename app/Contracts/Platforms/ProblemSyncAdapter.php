<?php

namespace App\Contracts\Platforms;

use Illuminate\Support\Collection;

interface ProblemSyncAdapter
{
    /**
     * Fetch problems from the platform.
     *
     * @param int $limit Maximum number of problems to fetch
     * @param string|null $contestId Optional contest ID to fetch problems for
     * @return Collection<\App\DataTransferObjects\Platform\ProblemDTO>
     */
    public function fetchProblems(int $limit = 200, ?string $contestId = null): Collection;

    /**
     * Check if the platform supports problem syncing.
     */
    public function supportsProblems(): bool;
}
