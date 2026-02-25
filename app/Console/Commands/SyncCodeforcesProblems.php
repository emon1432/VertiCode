<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCodeforcesProblems extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:codeforces-problems
                            {--limit=200 : Maximum number of problems to sync}
                            {--contest= : Specific contest ID to sync problems for}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Codeforces problems to the database';

    /**
     * Execute the console command.
     */
    public function handle(SyncPlatformProblemsAction $action, CodeforcesAdapter $adapter): int
    {
        $this->info('═════════════════════════════════════════════');
        $this->info('   Codeforces Problems Sync');
        $this->info('═════════════════════════════════════════════');
        $this->newLine();

        try {
            $platform = Platform::where('name', 'codeforces')->first();

            if (!$platform) {
                $this->error('✗ Codeforces platform not found in database.');
                return Command::FAILURE;
            }

            $limit = (int) $this->option('limit');
            $contestId = $this->option('contest');
            $force = $this->option('force');

            $this->line("Configuration:");
            $this->line("  • Limit: $limit problems");
            if ($contestId) {
                $this->line("  • Contest ID: $contestId");
            }
            $this->line("  • Force: " . ($force ? 'Yes' : 'No'));
            $this->newLine();

            $this->line('🔄 Starting sync...');
            $result = $action->execute($platform, $adapter, null, $limit);

            if ($result['success']) {
                $this->newLine();
                $this->info("✓ {$result['message']}");

                $platform->refresh();
                $this->newLine();
                $this->line("Statistics:");
                $this->line("  • Total problems: {$platform->problems()->count()}");
                $this->line("  • Last synced: {$platform->last_problem_sync_at->format('Y-m-d H:i:s')}");

                return Command::SUCCESS;
            } else {
                $this->error("✗ {$result['message']}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            Log::error('Codeforces problems sync failed', ['error' => $e->getMessage()]);
            $this->error("✗ An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
