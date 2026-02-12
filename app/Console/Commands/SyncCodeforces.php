<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCodeforces extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:codeforces
                            {--contests-only : Sync only contests}
                            {--problems-only : Sync only problems}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync both Codeforces contests and problems to the database';

    /**
     * Execute the console command.
     */
    public function handle(
        SyncPlatformContestsAction $contestsAction,
        SyncPlatformProblemsAction $problemsAction,
        CodeforcesAdapter $adapter
    ): int {
        $this->info('═══════════════════════════════════════════');
        $this->info('   Codeforces Platform Sync');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        try {
            // Get Codeforces platform
            $platform = Platform::where('name', 'codeforces')->first();

            if (!$platform) {
                $this->error('✗ Codeforces platform not found in database.');
                return Command::FAILURE;
            }

            $syncContests = !$this->option('problems-only');
            $syncProblems = !$this->option('contests-only');
            $force = $this->option('force');

            // Sync contests
            if ($syncContests) {
                $this->line('🔄 Syncing contests...');
                $result = $contestsAction->execute($platform, $adapter);

                if ($result['success']) {
                    $this->info("✓ {$result['message']}");
                    $platform->refresh();
                    $this->newLine();
                    $this->line("Statistics:");
                    $this->line("  • Total contests: {$platform->contests()->count()}");
                    $this->line("  • Last synced: {$platform->last_contest_sync_at->format('Y-m-d H:i:s')}");
                } else {
                    $this->error("✗ {$result['message']}");
                }
                $this->newLine();
            }

            // Sync problems
            if ($syncProblems) {
                $this->line('🔄 Syncing problems...');
                $result = $problemsAction->execute($platform, $adapter);

                if ($result['success']) {
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
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Codeforces sync failed', ['error' => $e->getMessage()]);
            $this->error("✗ An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
