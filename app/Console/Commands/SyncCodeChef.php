<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\CodeChef\CodeChefAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCodeChef extends Command
{
    protected $signature = 'sync:codechef
                            {--contests-only : Sync only contests}
                            {--problems-only : Sync only problems}
                            {--contest-limit=787 : Maximum number of contests to sync}
                            {--problem-limit=2000 : Maximum number of problems to sync}
                            {--force : Force sync even if recently synced}';

    protected $description = 'Sync both CodeChef contests and problems to the database';

    public function handle(
        SyncPlatformContestsAction $contestsAction,
        SyncPlatformProblemsAction $problemsAction,
        CodeChefAdapter $adapter
    ): int {
        $this->info('═══════════════════════════════════════════');
        $this->info('   CodeChef Platform Sync');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        try {
            $platform = Platform::where('name', 'codechef')->first();

            if (!$platform) {
                $this->error('✗ CodeChef platform not found in database.');
                return Command::FAILURE;
            }

            $syncContests = !$this->option('problems-only');
            $syncProblems = !$this->option('contests-only');
            $contestLimit = (int) $this->option('contest-limit');
            $problemLimit = (int) $this->option('problem-limit');

            if ($syncContests) {
                $this->line('🔄 Syncing contests...');
                $result = $contestsAction->execute($platform, $adapter, $contestLimit);

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

            if ($syncProblems) {
                $this->line('🔄 Syncing problems...');
                $result = $problemsAction->execute($platform, $adapter, null, $problemLimit);

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
            Log::error('CodeChef sync failed', ['error' => $e->getMessage()]);
            $this->error("✗ An error occurred: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
