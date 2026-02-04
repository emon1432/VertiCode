<?php

namespace App\Console\Commands;

use App\Actions\SyncPlatformProfileAction;
use App\Models\User;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\Spoj\SpojAdapter;
use Illuminate\Console\Command;

class TestSpojSync extends Command
{
    protected $signature = 'test:spoj {handle} {--sync : Run full sync}';

    protected $description = 'Test SPOJ profile fetching (submissions disabled due to Cloudflare)';

    public function handle(SpojAdapter $adapter, SyncPlatformProfileAction $syncAction): int
    {
        $handle = $this->argument('handle');
        $runSync = $this->option('sync');

        $this->info("Testing SPOJ for handle: {$handle}");
        $this->warn("Note: SPOJ uses aggressive Cloudflare protection and may block requests.");
        $this->newLine();

        try {
            // Test profile fetch
            $this->info('1. Fetching profile...');
            $profile = $adapter->fetchProfile($handle);

            $this->line("   ✓ Handle: {$profile->handle}");
            $this->line("   ✓ Rank: " . ($profile->raw['rank'] ?? 'Unranked'));
            $this->line("   ✓ Total Solved: {$profile->totalSolved}");
            $this->line("   ✓ Join Date: " . ($profile->raw['join_date'] ?? 'N/A'));
            $this->line("   ✓ Problem Categories: {$profile->raw['problem_slugs_count']}");
            $this->newLine();

            $this->info('2. Submissions:');
            $this->line("   ℹ Submissions are disabled for SPOJ due to Cloudflare protection.");
            $this->line("   ℹ Using profile's total_solved count instead.");
            $this->newLine();

            // Optionally run full sync
            if ($runSync) {
                $this->info('3. Running full sync...');

                // Find user and platform
                $user = User::first();
                if (!$user) {
                    $this->error('   ✗ No user found in database');
                    return 1;
                }

                $platform = Platform::where('name', 'spoj')->first();
                if (!$platform) {
                    $this->error('   ✗ SPOJ platform not found in database. Run platform seeder.');
                    return 1;
                }

                $platformProfile = PlatformProfile::firstOrCreate(
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
                $this->line("   ✓ Rank: " . ($platformProfile->raw['rank'] ?? 'Unranked'));
            }

            $this->newLine();
            $this->info('✓ Test completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->error('✗ Test failed: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'Cloudflare') || str_contains($e->getMessage(), '403')) {
                $this->newLine();
                $this->warn('⚠ SPOJ Cloudflare Protection Detected');
                $this->line('SPOJ has aggressive bot protection and frequently blocks automated requests.');
                $this->line('This is a known limitation and is expected behavior.');
                $this->line('Solutions:');
                $this->line('  • Try from a production server with a different IP');
                $this->line('  • Try again later (Cloudflare may temporarily allow requests)');
                $this->line('  • Use a proxy service or browser automation');
                $this->line('  • Profile sync works when available; submissions are optional');
            }

            if ($this->output->isVerbose()) {
                $this->newLine();
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
}
