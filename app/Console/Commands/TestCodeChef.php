<?php

namespace App\Console\Commands;

use App\Models\Platform;
use App\Platforms\CodeChef\CodeChefAdapter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCodeChef extends Command
{
    protected $signature = 'test:codechef {--show-data : Display sample data}';

    protected $description = 'Test CodeChef adapter and display sample contests/problems';

    public function handle(CodeChefAdapter $adapter): int
    {
        $this->info('═════════════════════════════════════════════');
        $this->info('   CodeChef Adapter Test');
        $this->info('═════════════════════════════════════════════');
        $this->newLine();

        try {
            $platform = Platform::where('name', 'codechef')->first();

            if (!$platform) {
                $this->error('✗ CodeChef platform not found in database.');
                return Command::FAILURE;
            }

            // Test contests fetching
            $this->line('🧪 Testing contests fetch...');
            $contests = $adapter->fetchContests(5);
            $this->info("✓ Successfully fetched {$contests->count()} contests");

            if ($this->option('show-data') && $contests->isNotEmpty()) {
                $this->newLine();
                $this->line('📋 Sample Contests:');
                $this->line(str_repeat('─', 100));

                foreach ($contests->take(3) as $contest) {
                    $startTime = $contest->startTime?->format('Y-m-d H:i:s') ?? 'N/A';
                    $this->line("ID: {$contest->platformContestId}");
                    $this->line("Name: {$contest->name}");
                    $this->line("Type: {$contest->type->value}");
                    $this->line("Phase: {$contest->phase}");
                    $this->line("Start Time: {$startTime}");
                    $this->line("URL: {$contest->url}");
                    $this->line(str_repeat('─', 100));
                }
            }

            // Test problems fetching
            $this->line('🧪 Testing problems fetch...');
            $problems = $adapter->fetchProblems(5);
            $this->info("✓ Successfully fetched {$problems->count()} problems");

            if ($this->option('show-data') && $problems->isNotEmpty()) {
                $this->newLine();
                $this->line('📋 Sample Problems:');
                $this->line(str_repeat('─', 100));

                foreach ($problems->take(3) as $problem) {
                    $accuracy = $problem->accuracy ?? 'N/A';
                    $this->line("ID: {$problem->platformProblemId}");
                    $this->line("Name: {$problem->name}");
                    $this->line("Code: {$problem->code}");
                    $this->line("Difficulty: {$problem->difficulty->value}");
                    $this->line("Accuracy: {$accuracy}%");
                    $this->line("Solved: {$problem->solvedCount}");
                    $this->line("URL: {$problem->url}");
                    $this->line("Tags: " . implode(', ', $problem->tags));
                    $this->line(str_repeat('─', 100));
                }
            }

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
            Log::error('CodeChef test failed', ['error' => $e->getMessage()]);
            $this->error("✗ Test failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
