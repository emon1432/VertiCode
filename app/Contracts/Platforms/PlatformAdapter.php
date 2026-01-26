<?php

namespace App\Contracts\Platforms;

use App\DataTransferObjects\Platform\ProfileDTO;
use Illuminate\Support\Collection;

interface PlatformAdapter
{
    public function platform(): string;
    public function supportsSubmissions(): bool;
    public function fetchProfile(string $handle): ProfileDTO;
    public function fetchSubmissions(string $handle): Collection;
    public function profileUrl(string $handle): string;
}
