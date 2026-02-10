<?php

namespace App\Console\Commands;

use App\Jobs\SyncPlatformContestsJob;
use App\Jobs\SyncPlatformProblemsJob;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use App\Platforms\HackerRank\HackerRankAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use App\Platforms\Spoj\SpojAdapter;
use App\Platforms\Timus\TimusAdapter;
use App\Platforms\Uva\UvaAdapter;
use Illuminate\Console\Command;

class SyncAllPlatformsCommand extends Command
{
    protected $signature = 'platforms:sync-all
                            {--contests : Only sync contests}
                            {--problems : Only sync problems}';

    protected $description = 'Sync contests and problems from all active platforms';

    protected array $platformAdapters = [
        'codeforces' => CodeforcesAdapter::class,
        'leetcode' => LeetCodeAdapter::class,
        'codechef' => CodeChefAdapter::class,
        'atcoder' => AtCoderAdapter::class,
        'hackerrank' => HackerRankAdapter::class,
        'spoj' => SpojAdapter::class,
        'hackerearth' => HackerEarthAdapter::class,
        'timus' => TimusAdapter::class,
        'uva' => UvaAdapter::class,
    ];

    public function handle(): int
    {
        $platforms = Platform::active()->get();

        if ($platforms->isEmpty()) {
            $this->error('No active platforms found.');
            return self::FAILURE;
        }

        $syncContests = !$this->option('problems') || $this->option('contests');
        $syncProblems = !$this->option('contests') || $this->option('problems');

        $this->info("Starting sync for {$platforms->count()} platforms...");

        foreach ($platforms as $platform) {
            $adapterClass = $this->platformAdapters[$platform->name] ?? null;

            if (!$adapterClass || !class_exists($adapterClass)) {
                $this->warn("Skipping {$platform->name}: Adapter not found");
                continue;
            }

            $this->info("Processing {$platform->display_name}...");

            if ($syncContests) {
                SyncPlatformContestsJob::dispatch($platform->id, $adapterClass);
                $this->line("  ✓ Contests sync job dispatched");
            }

            if ($syncProblems) {
                SyncPlatformProblemsJob::dispatch($platform->id, $adapterClass);
                $this->line("  ✓ Problems sync job dispatched");
            }
        }

        $this->info('All sync jobs have been dispatched to the queue.');
        return self::SUCCESS;
    }
}
