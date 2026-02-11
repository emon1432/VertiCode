<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAtCoderContests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:atcoder-contests
                            {--limit=100 : Maximum number of contests to fetch}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync AtCoder contests to the database';

    /**
     * Execute the console command.
     */
    public function handle(SyncPlatformContestsAction $action, AtCoderAdapter $adapter): int
    {
        $this->info('Starting AtCoder contests sync...');
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
            if (!$force && !$platform->needsContestSync()) {
                $lastSync = $platform->last_contest_sync_at?->diffForHumans() ?? 'never';
                $this->info("✓ AtCoder contests were synced {$lastSync}");
                $this->line('  Use --force to sync anyway');
                return Command::SUCCESS;
            }

            // Display sync info
            $this->line("Platform: {$platform->display_name}");
            $this->line("Status: {$platform->status}");
            $this->line("Last Sync: " . ($platform->last_contest_sync_at?->format('Y-m-d H:i:s') ?? 'Never'));
            $this->newLine();

            // Start sync with progress bar
            $this->info('Fetching contests from AtCoder...');

            // Execute sync
            $result = $action->execute($platform, $adapter);

            $this->newLine();

            // Display results
            if ($result['success']) {
                $this->info("✓ Successfully synced {$result['synced']} contests");

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
                $this->line("  • Total contests: {$platform->contests()->count()}");
                $this->line("  • Last synced: {$platform->last_contest_sync_at->format('Y-m-d H:i:s')}");

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

            Log::error('AtCoder contests sync command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
