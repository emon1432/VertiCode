<?php

namespace App\Platforms\Codeforces;

use App\Contracts\Platforms\PlatformAdapter;
use App\DataTransferObjects\Platform\ProfileDTO;
use Illuminate\Support\Collection;

class CodeforcesAdapter implements PlatformAdapter
{
    public function platform(): string
    {
        return 'codeforces';
    }

    public function supportsSubmissions(): bool
    {
        return true;
    }

    public function fetchProfile(string $handle): ProfileDTO
    {
        // implementation will come later
        throw new \RuntimeException('Not implemented');
    }

    public function fetchSubmissions(string $handle): Collection
    {
        // implementation will come later
        throw new \RuntimeException('Not implemented');
    }
}
