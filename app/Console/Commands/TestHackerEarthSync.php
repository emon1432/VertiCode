<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\PlatformProfile;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use Illuminate\Console\Command;

class TestHackerEarthSync extends Command
{
    protected $signature = 'test:hackerearth {profile_id}';
    protected $description = 'Test HackerEarth sync for a specific profile';

    public function handle(SyncPlatformProfileAction $syncAction, HackerEarthAdapter $adapter): int
    {
        $profileId = $this->argument('profile_id');

        $profile = PlatformProfile::with('platform')->find($profileId);

        if (!$profile) {
            $this->error("Profile #{$profileId} not found");
            return 1;
        }

        if ($profile->platform->name !== 'hackerearth') {
            $this->error("Profile #{$profileId} is not a HackerEarth profile");
            return 1;
        }

        $this->info("Testing HackerEarth sync for handle: {$profile->handle}");
        $this->info("Profile URL: {$profile->profile_url}");
        $this->newLine();

        // Test fetching profile
        $this->info("1. Fetching profile data...");
        try {
            $profileDto = $adapter->fetchProfile($profile->handle);
            $this->line("   ✓ Handle: {$profileDto->handle}");
            $this->line("   ✓ Rating: " . ($profileDto->rating ?? 'null'));
            $this->line("   ✓ Total Solved (from DTO): {$profileDto->totalSolved}");
            $this->line("   ✓ Raw data keys: " . implode(', ', array_keys($profileDto->raw)));
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to fetch profile: {$e->getMessage()}");
            return 1;
        }

        $this->newLine();

        // Test fetching submissions
        $this->info("2. Fetching submissions...");
        try {
            $submissions = $adapter->fetchSubmissions($profile->handle);
            $this->line("   ✓ Total submissions fetched: " . $submissions->count());

            $acceptedCount = $submissions->filter(fn($s) => $s->verdict->value === 'accepted')->count();
            $this->line("   ✓ Accepted submissions: {$acceptedCount}");

            $uniqueProblems = $submissions->unique(fn($s) => $s->problemId)->count();
            $this->line("   ✓ Unique problems solved: {$uniqueProblems}");

            if ($submissions->isNotEmpty()) {
                $this->newLine();
                $this->line("   First 5 submissions:");
                foreach ($submissions->take(5) as $sub) {
                    $this->line("   - [{$sub->verdict->value}] {$sub->problemName}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to fetch submissions: {$e->getMessage()}");
            return 1;
        }

        $this->newLine();

        // Ask if user wants to sync
        if ($this->confirm('Do you want to run the full sync?', true)) {
            $this->info("3. Running full sync...");

            // Temporarily clear last_synced_at to bypass cooldown
            $profile->update(['last_synced_at' => null]);

            try {
                $syncAction->execute($profile, $adapter);
                $profile->refresh();

                $this->info("   ✓ Sync completed successfully!");
                $this->line("   ✓ Rating: " . ($profile->rating ?? 'null'));
                $this->line("   ✓ Total Solved: {$profile->total_solved}");
                $this->line("   ✓ Last Synced: {$profile->last_synced_at}");
            } catch (\Exception $e) {
                $this->error("   ✗ Sync failed: {$e->getMessage()}");
                return 1;
            }
        }

        $this->newLine();
        $this->info("Test completed!");

        return 0;
    }
}
