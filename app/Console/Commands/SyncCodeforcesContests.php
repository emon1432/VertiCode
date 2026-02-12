<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCodeforcesContests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:codeforces-contests
                            {--limit=100 : Maximum number of contests to sync}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync Codeforces contests to the database';

    /**
     * Execute the console command.
     */
    public function handle(SyncPlatformContestsAction $action, CodeforcesAdapter $adapter): int
    {
        $this->info('═════════════════════════════════════════════');
        $this->info('   Codeforces Contests Sync');
        $this->info('═════════════════════════════════════════════');
        $this->newLine();

        try {
            $platform = Platform::where('name', 'codeforces')->first();

            if (!$platform) {
                $this->error('✗ Codeforces platform not found in database.');
                return Command::FAILURE;
            }

            $limit = (int) $this->option('limit');
            $force = $this->option('force');

            $this->line("Configuration:");
            $this->line("  • Limit: $limit contests");
            $this->line("  • Force: " . ($force ? 'Yes' : 'No'));
            $this->newLine();

            $this->line('🔄 Starting sync...');
            $result = $action->execute($platform, $adapter);

            if ($result['success']) {
                $this->newLine();
                $this->info("✓ {$result['message']}");

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
            Log::error('Codeforces contests sync failed', ['error' => $e->getMessage()]);
            $this->error("✗ An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
