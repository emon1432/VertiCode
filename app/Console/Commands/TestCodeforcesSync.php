<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\User;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Console\Command;

class TestCodeforcesSync extends Command
{
    protected $signature = 'test:codeforces {handle} {--sync : Run full sync}';

    protected $description = 'Test Codeforces profile and submissions fetching';

    public function handle(CodeforcesAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing Codeforces for handle: {$handle}");
        $this->newLine();

        try {
            // Test profile fetch
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Handle: {$profile->handle}");
            $this->line("   ✓ Current Rating: " . ($profile->rating ?? 'N/A'));
            $this->line("   ✓ Max Rating: " . ($profile->raw['max_rating'] ?? 'N/A'));
            $this->line("   ✓ Rank: " . ($profile->raw['rank'] ?? 'N/A'));
            $this->line("   ✓ Max Rank: " . ($profile->raw['max_rank'] ?? 'N/A'));
            $this->line("   ✓ Contests Participated: " . count($profile->raw['contest_history'] ?? []));
            $this->line("   ✓ Rating Graph Points: " . count($profile->raw['rating_graph_data'] ?? []));
            $this->newLine();

            // Show last 5 contests
            if (!empty($profile->raw['contest_history'])) {
                $this->info('   Last 5 Contests:');
                $contests = array_slice($profile->raw['contest_history'], 0, 5);
                foreach ($contests as $contest) {
                    $this->line(sprintf(
                        "     • %s | Rank: %d | Solved: %d | Rating: %+d → %d",
                        substr($contest['contest_name'], 0, 40),
                        $contest['rank'],
                        $contest['solved_count'],
                        $contest['rating_change'],
                        $contest['new_rating']
                    ));
                }
                $this->newLine();
            }

            // Test submissions fetch
            $this->info('2. Fetching submissions...');
            $submissions = $adapter->fetchSubmissions($handle);

            $this->line("   ✓ Total AC Submissions: {$submissions->count()}");
            $uniqueProblems = $submissions->unique('problemId')->count();
            $this->line("   ✓ Unique Problems Solved: {$uniqueProblems}");
            $this->newLine();

            // Show first 5 submissions with tags
            if ($submissions->isNotEmpty()) {
                $this->info('   First 5 Accepted Submissions:');
                $submissions->take(5)->each(function ($submission) {
                    $tags = implode(', ', array_slice($submission->raw['tags'] ?? [], 0, 3));
                    $tagsStr = $tags ? " | Tags: {$tags}" : '';
                    $this->line(sprintf(
                        "     • %s (%s) | Difficulty: %s%s",
                        substr($submission->problemName, 0, 35),
                        $submission->problemId,
                        $submission->difficulty ?? 'N/A',
                        $tagsStr
                    ));
                });
                $this->newLine();
            }

            // Optionally run full sync
            if ($runSync) {
                $this->info('3. Running full sync...');

                // Find user and platform
                $user = User::whereHas('platformProfiles', function ($query) use ($handle) {
                    $query->where('handle', $handle)->whereHas('platform', function ($q) {
                        $q->where('name', 'codeforces');
                    });
                })->first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'codeforces')->first();
                if (!$platform) {
                    $this->error('   ✗ Codeforces platform not found in database. Run platform seeder.');
                    return 1;
                }

                $platformProfile = PlatformProfile::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'platform_id' => $platform->id,
                        'handle' => $handle,
                    ],
                    [
                        'profile_url' => $adapter->profileUrl($handle),
                        'status' => 'Active',
                    ]
                );

                // Bypass cooldown for testing
                $platformProfile->last_synced_at = null;
                $platformProfile->save();

                $syncAction->execute($platformProfile, $adapter);

                $platformProfile->refresh();
                $this->line("   ✓ Sync completed!");
                $this->line("   ✓ Total Solved: {$platformProfile->total_solved}");
                $this->line("   ✓ Rating: " . ($platformProfile->rating ?? 'N/A'));
                $this->line("   ✓ Max Rating: " . ($platformProfile->raw['max_rating'] ?? 'N/A'));
            }

            $this->newLine();
            $this->info('✓ Test completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Test failed: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}
