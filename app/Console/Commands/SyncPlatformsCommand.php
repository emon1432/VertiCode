<?php

namespace App\Console\Commands;

use App\Services\PlatformSyncService;
use Illuminate\Console\Command;

class SyncPlatformsCommand extends Command
{
    protected $signature = 'platforms:sync
                            {--contests : Only sync contests}
                            {--problems : Only sync problems}
                            {--force : Force sync even if recently completed}';

    protected $description = 'Dispatch platform-specific sync jobs for contests and problems';

    public function handle(PlatformSyncService $service): int
    {
        $onlyNeeded = !$this->option('force');
        $syncContests = !$this->option('problems') || $this->option('contests');
        $syncProblems = !$this->option('contests') || $this->option('problems');

        $this->info('Dispatching platform-specific sync jobs...' . "\n");

        if ($syncContests && $syncProblems) {
            $results = $service->dispatchAllSyncs($onlyNeeded);
        } elseif ($syncContests) {
            $results = $service->dispatchContestSyncs($onlyNeeded);
        } else {
            $results = $service->dispatchProblemSyncs($onlyNeeded);
        }

        // Display results
        foreach ($results as $result) {
            $platform = $result['platform'];
            $type = $result['type'];
            $status = $result['status'];

            match ($status) {
                'dispatched' => $this->line("  <fg=green>✓</> {$platform} ({$type}) - Job dispatched"),
                'skipped' => $this->line("  <fg=yellow>⊘</> {$platform} ({$type}) - {$result['reason']}"),
                'failed' => $this->line("  <fg=red>✗</> {$platform} ({$type}) - {$result['reason']}"),
                default => $this->line("  {$platform} ({$type}) - {$status}"),
            };
        }

        $this->newLine();
        $dispatched = $results->where('status', 'dispatched')->count();
        $this->info("Total jobs dispatched: {$dispatched}");

        return self::SUCCESS;
    }
}
