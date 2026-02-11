<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAtCoder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:atcoder
                            {--contests-only : Sync only contests}
                            {--problems-only : Sync only problems}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync both AtCoder contests and problems to the database';

    /**
     * Execute the console command.
     */
    public function handle(
        SyncPlatformContestsAction $contestsAction,
        SyncPlatformProblemsAction $problemsAction,
        AtCoderAdapter $adapter
    ): int {
        $this->info('═══════════════════════════════════════════');
        $this->info('   AtCoder Platform Sync');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        try {
            // Get AtCoder platform
            $platform = Platform::where('name', 'atcoder')->first();

            if (!$platform) {
                $this->error('✗ AtCoder platform not found in database.');
                $this->line('  Please run: php artisan db:seed --class=PlatformSeeder');
                return Command::FAILURE;
            }

            $contestsOnly = $this->option('contests-only');
            $problemsOnly = $this->option('problems-only');
            $force = $this->option('force');

            // Display platform info
            $this->line("Platform: {$platform->display_name}");
            $this->line("Status: {$platform->status}");
            $this->newLine();

            $overallSuccess = true;
            $syncedContests = 0;
            $syncedProblems = 0;

            // Sync contests (unless problems-only)
            if (!$problemsOnly) {
                $this->info('─────────────────────────────────────────');
                $this->info('  Syncing Contests');
                $this->info('─────────────────────────────────────────');
                $this->newLine();

                if (!$force && !$platform->needsContestSync()) {
                    $lastSync = $platform->last_contest_sync_at?->diffForHumans() ?? 'never';
                    $this->line("✓ Contests were synced {$lastSync}");
                } else {
                    $this->line('Fetching contests from AtCoder...');
                    $result = $contestsAction->execute($platform, $adapter, 100);

                    if ($result['success']) {
                        $syncedContests = $result['synced'];
                        $this->info("✓ Synced {$syncedContests} contests");
                    } else {
                        $this->error("✗ Contest sync failed: {$result['message']}");
                        $overallSuccess = false;
                    }
                }
                $this->newLine();
            }

            // Sync problems (unless contests-only)
            if (!$contestsOnly) {
                $this->info('─────────────────────────────────────────');
                $this->info('  Syncing Problems');
                $this->info('─────────────────────────────────────────');
                $this->newLine();

                if (!$force && !$platform->needsProblemSync()) {
                    $lastSync = $platform->last_problem_sync_at?->diffForHumans() ?? 'never';
                    $this->line("✓ Problems were synced {$lastSync}");
                } else {
                    $this->line('Fetching problems from AtCoder...');
                    $result = $problemsAction->execute($platform, $adapter);

                    if ($result['success']) {
                        $syncedProblems = $result['synced'];
                        $this->info("✓ Synced {$syncedProblems} problems");
                    } else {
                        $this->error("✗ Problem sync failed: {$result['message']}");
                        $overallSuccess = false;
                    }
                }
                $this->newLine();
            }

            // Display summary
            $platform->refresh();
            $this->info('═══════════════════════════════════════════');
            $this->info('  Sync Summary');
            $this->info('═══════════════════════════════════════════');
            $this->newLine();

            if ($syncedContests > 0 || !$problemsOnly) {
                $this->line("Contests:");
                $this->line("  • Synced this run: {$syncedContests}");
                $this->line("  • Total in database: {$platform->contests()->count()}");
                $this->line("  • Last synced: " . ($platform->last_contest_sync_at?->format('Y-m-d H:i:s') ?? 'Never'));
                $this->newLine();
            }

            if ($syncedProblems > 0 || !$contestsOnly) {
                $this->line("Problems:");
                $this->line("  • Synced this run: {$syncedProblems}");
                $this->line("  • Total in database: {$platform->problems()->count()}");
                $this->line("  • Last synced: " . ($platform->last_problem_sync_at?->format('Y-m-d H:i:s') ?? 'Never'));
                $this->newLine();
            }

            if ($overallSuccess) {
                $this->info('✓ AtCoder sync completed successfully!');
                return Command::SUCCESS;
            } else {
                $this->warn('⚠ AtCoder sync completed with errors');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("✗ Sync failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            Log::error('AtCoder sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
