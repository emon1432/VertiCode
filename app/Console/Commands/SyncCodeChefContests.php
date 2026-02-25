<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Models\Platform;
use App\Platforms\CodeChef\CodeChefAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCodeChefContests extends Command
{
    protected $signature = 'sync:codechef-contests
                            {--limit=100 : Maximum number of contests to sync}
                            {--force : Force sync even if recently synced}';

    protected $description = 'Sync CodeChef contests to the database';

    public function handle(SyncPlatformContestsAction $action, CodeChefAdapter $adapter): int
    {
        $this->info('═════════════════════════════════════════════');
        $this->info('   CodeChef Contests Sync');
        $this->info('═════════════════════════════════════════════');
        $this->newLine();

        try {
            $platform = Platform::where('name', 'codechef')->first();

            if (!$platform) {
                $this->error('✗ CodeChef platform not found in database.');
                return Command::FAILURE;
            }

            $limit = (int) $this->option('limit');
            $force = $this->option('force');

            $this->line("Configuration:");
            $this->line("  • Limit: $limit contests");
            $this->line("  • Force: " . ($force ? 'Yes' : 'No'));
            $this->newLine();

            $this->line('🔄 Starting sync...');
            $result = $action->execute($platform, $adapter, $limit);

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
            Log::error('CodeChef contests sync failed', ['error' => $e->getMessage()]);
            $this->error("✗ An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
