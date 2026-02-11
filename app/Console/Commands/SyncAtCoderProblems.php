<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAtCoderProblems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:atcoder-problems
                            {--limit=500 : Maximum number of problems to fetch}
                            {--contest= : Optional contest ID to sync problems for}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AtCoder problems to the database';

    /**
     * Execute the console command.
     */
    public function handle(SyncPlatformProblemsAction $action, AtCoderAdapter $adapter): int
    {
        $this->info('Starting AtCoder problems sync...');
        $this->newLine();

        try {
            // Get AtCoder platform
            $platform = Platform::where('name', 'atcoder')->first();

            if (!$platform) {
                $this->error('✗ AtCoder platform not found in database.');
                $this->line('  Please run: php artisan db:seed --class=PlatformSeeder');
                return Command::FAILURE;
            }

            // Check if sync is needed
            $force = $this->option('force');
            $contestId = $this->option('contest');

            if (!$force && !$contestId && !$platform->needsProblemSync()) {
                $lastSync = $platform->last_problem_sync_at?->diffForHumans() ?? 'never';
                $this->info("✓ AtCoder problems were synced {$lastSync}");
                $this->line('  Use --force to sync anyway');
                return Command::SUCCESS;
            }

            // Display sync info
            $this->line("Platform: {$platform->display_name}");
            $this->line("Status: {$platform->status}");
            $this->line("Last Sync: " . ($platform->last_problem_sync_at?->format('Y-m-d H:i:s') ?? 'Never'));
            if ($contestId) {
                $this->line("Contest Filter: {$contestId}");
            }
            $this->newLine();

            // Start sync with progress bar
            $this->info('Fetching problems from AtCoder...');

            // Execute sync
            $result = $action->execute($platform, $adapter, $contestId);

            $this->newLine();

            // Display results
            if ($result['success']) {
                $this->info("✓ Successfully synced {$result['synced']} problems");

                // Show errors if any
                if (!empty($result['errors'])) {
                    $this->warn("\nWarnings:");
                    foreach ($result['errors'] as $error) {
                        $this->line("  • {$error}");
                    }
                }

                // Show statistics
                $platform->refresh();
                $this->newLine();
                $this->line("Statistics:");
                $this->line("  • Total problems: {$platform->problems()->count()}");
                $this->line("  • Sync count: {$platform->problem_sync_count}");
                $this->line("  • Last synced: {$platform->last_problem_sync_at->format('Y-m-d H:i:s')}");

                return Command::SUCCESS;
            } else {
                $this->error("✗ {$result['message']}");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("✗ Sync failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            Log::error('AtCoder problems sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
