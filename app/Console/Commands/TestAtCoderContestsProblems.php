<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformContestsAction;
use App\Actions\SyncPlatformProblemsAction;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use Illuminate\Console\Command;

class TestAtCoderContestsProblems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:atcoder-sync
                            {--contests-only : Test only contests sync}
                            {--problems-only : Test only problems sync}
                            {--show-data : Display sample data after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AtCoder contests and problems sync functionality';

    /**
     * Execute the console command.
     */
    public function handle(
        SyncPlatformContestsAction $contestsAction,
        SyncPlatformProblemsAction $problemsAction,
        AtCoderAdapter $adapter
    ): int {
        $this->info('╔═══════════════════════════════════════════╗');
        $this->info('║   AtCoder Sync Test                      ║');
        $this->info('╚═══════════════════════════════════════════╝');
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
            $showData = $this->option('show-data');

            // Display platform info
            $this->table(
                ['Property', 'Value'],
                [
                    ['Platform', $platform->display_name],
                    ['Status', $platform->status],
                    ['Base URL', $platform->base_url],
                    ['Contests in DB', $platform->contests()->count()],
                    ['Problems in DB', $platform->problems()->count()],
                    ['Last Contest Sync', $platform->last_contest_sync_at?->format('Y-m-d H:i:s') ?? 'Never'],
                    ['Last Problem Sync', $platform->last_problem_sync_at?->format('Y-m-d H:i:s') ?? 'Never'],
                ]
            );
            $this->newLine();

            // Test contest sync (unless problems-only)
            if (!$problemsOnly) {
                $this->info('─── Testing Contest Sync ───────────────────');
                $this->newLine();

                $this->line('Attempting to fetch contests...');
                $contestsBefore = $platform->contests()->count();

                $result = $contestsAction->execute($platform, $adapter);

                if ($result['success']) {
                    $this->info("✓ Contest sync successful!");
                    $this->line("  • {$result['synced']} contests synced");
                    $this->line("  • Total contests in DB: {$platform->contests()->count()}");

                    if (!empty($result['errors']) && $showData) {
                        $this->warn("  • Errors: " . count($result['errors']));
                        foreach (array_slice($result['errors'], 0, 3) as $error) {
                            $this->line("    - {$error}");
                        }
                    }

                    // Show sample contests
                    if ($showData) {
                        $this->newLine();
                        $this->line('Sample contests:');
                        $contests = $platform->contests()->latest()->take(5)->get();

                        $tableData = $contests->map(fn($c) => [
                            substr($c->name, 0, 40),
                            $c->type,
                            $c->start_time?->format('Y-m-d'),
                            $c->is_rated ? 'Yes' : 'No',
                            $c->participant_count ?? 'N/A',
                        ])->toArray();

                        $this->table(
                            ['Contest', 'Type', 'Date', 'Rated', 'Participants'],
                            $tableData
                        );
                    }
                } else {
                    $this->error("✗ Contest sync failed!");
                    $this->line("  • Error: {$result['message']}");
                }

                $this->newLine();
            }

            // Test problem sync (unless contests-only)
            if (!$contestsOnly) {
                $this->info('─── Testing Problem Sync ───────────────────');
                $this->newLine();

                $this->line('Attempting to fetch problems...');
                $problemsBefore = $platform->problems()->count();

                $result = $problemsAction->execute($platform, $adapter);

                if ($result['success']) {
                    $this->info("✓ Problem sync successful!");
                    $this->line("  • {$result['synced']} problems synced");
                    $this->line("  • Total problems in DB: {$platform->problems()->count()}");

                    if (!empty($result['errors']) && $showData) {
                        $this->warn("  • Errors: " . count($result['errors']));
                        foreach (array_slice($result['errors'], 0, 3) as $error) {
                            $this->line("    - {$error}");
                        }
                    }

                    // Show sample problems
                    if ($showData) {
                        $this->newLine();
                        $this->line('Sample problems:');
                        $problems = $platform->problems()->latest()->take(5)->get();

                        $tableData = $problems->map(fn($p) => [
                            $p->code ?? 'N/A',
                            substr($p->name, 0, 35),
                            $p->difficulty?->value ?? 'N/A',
                            $p->rating ?? 'N/A',
                            $p->points ?? 'N/A',
                        ])->toArray();

                        $this->table(
                            ['Code', 'Problem', 'Difficulty', 'Rating', 'Points'],
                            $tableData
                        );
                    }
                } else {
                    $this->error("✗ Problem sync failed!");
                    $this->line("  • Error: {$result['message']}");
                }

                $this->newLine();
            }

            // Final summary
            $platform->refresh();
            $this->info('╔═══════════════════════════════════════════╗');
            $this->info('║   Test Summary                            ║');
            $this->info('╚═══════════════════════════════════════════╝');
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Contests', $platform->contests()->count()],
                    ['Total Problems', $platform->problems()->count()],
                    ['Last Contest Sync', $platform->last_contest_sync_at?->diffForHumans() ?? 'Never'],
                    ['Last Problem Sync', $platform->last_problem_sync_at?->diffForHumans() ?? 'Never'],
                ]
            );

            $this->newLine();
            $this->info('✓ Test completed successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Test failed: {$e->getMessage()}");

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->line($e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }
}
