<?php

namespace App\Contracts\Platforms;

use App\DataTransferObjects\Platform\ProfileDTO;
use App\DataTransferObjects\Platform\SubmissionDTO;
use Illuminate\Support\Collection;

interface PlatformAdapter
{
    public function platform(): string;

    public function supportsSubmissions(): bool;

    public function fetchProfile(string $handle): ProfileDTO;

    /**
     * @return Collection<SubmissionDTO>
     */
    public function fetchSubmissions(string $handle): Collection;
}
