<?php

namespace App\Console\Commands;

use App\Platforms\HackerEarth\HackerEarthAdapter;
use Illuminate\Console\Command;

class DebugHackerEarthSync extends Command
{
    protected $signature = 'debug:hackerearth {handle}';
    protected $description = 'Debug HackerEarth submissions with detailed verdict info';

    public function handle(HackerEarthAdapter $adapter): int
    {
        $handle = $this->argument('handle');

        $this->info("Debugging HackerEarth submissions for: {$handle}");
        $this->newLine();

        try {
            $submissions = $adapter->fetchSubmissions($handle);

            $this->line("Total submissions returned by adapter: " . $submissions->count());
            $this->newLine();

            $byVerdict = [];
            foreach ($submissions as $sub) {
                $verdict = $sub->verdict->value;
                if (!isset($byVerdict[$verdict])) {
                    $byVerdict[$verdict] = [];
                }
                $byVerdict[$verdict][] = $sub;
            }

            foreach ($byVerdict as $verdict => $subs) {
                $this->line("Verdict '{$verdict}': " . count($subs) . " submissions");
            }

            $this->newLine();
            $uniqueProblems = $submissions->unique(fn($s) => $s->problemId)->count();
            $this->line("Unique problems: {$uniqueProblems}");

            $this->newLine();
            $this->line("All submissions:");
            foreach ($submissions as $idx => $sub) {
                $this->line("  " . ($idx + 1) . ". [{$sub->verdict->value}] {$sub->problemName} (ID: {$sub->problemId})");
            }

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
