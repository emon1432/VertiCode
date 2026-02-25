<?php

namespace App\Console\Commands;

use App\Services\Platforms\CodeChefDataCollector;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Actions\SyncPlatformContestsAction;
use App\DataTransferObjects\Platform\ContestDTO;
use App\Enums\Platform;
use App\Enums\ContestType;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncCodeChefMissingContests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:codechef-missing-contests {--force : Force sync even if contests already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing CodeChef contests referenced by problems';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Syncing missing CodeChef contests...');

        // Extract unique contest codes from problems
        $contestCodes = DB::table('problems')
            ->where('platform_id', 4)
            ->whereNotNull('raw')
            ->get()
            ->map(function ($problem) {
                $data = json_decode($problem->raw, true);
                return $data['contestCode'] ?? null;
            })
            ->filter(fn($code) => !empty($code) && $code !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($contestCodes)) {
            $this->warn('No contest codes found in problems');
            return 0;
        }

        $this->line("Found " . count($contestCodes) . " unique contest codes");

        // Check which contests already exist
        $existingCodes = DB::table('contests')
            ->where('platform_id', 4)
            ->whereIn('platform_contest_id', $contestCodes)
            ->pluck('platform_contest_id')
            ->toArray();

        $missingCodes = array_diff($contestCodes, $existingCodes);

        if (empty($missingCodes)) {
            $this->info('✓ All contests already synced');
            return 0;
        }

        $this->line("Missing " . count($missingCodes) . " contests");

        // Fetch missing contests
        $rawContests = collect($missingCodes)->mapWithKeys(function ($code) {
            usleep(500000); // 500ms delay
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'application/json',
                ])->timeout(30)->get("https://www.codechef.com/api/contests/$code");

                if ($response->ok() && isset($response->json()['data'])) {
                    return [$code => $response->json()['data']];
                }
            } catch (\Exception $e) {
                $this->warn("Failed to fetch contest $code: " . $e->getMessage());
            }
            return [];
        })->values();

        if ($rawContests->isEmpty()) {
            $this->error('Failed to fetch missing contests');
            return 1;
        }

        // Transform to DTOs
        $dtos = $rawContests->map(function ($contest) {
            $contestId = $contest['code'] ?? $contest['id'];
            $startTime = isset($contest['contest_start_date'])
                ? CarbonImmutable::parse($contest['contest_start_date'])
                : null;
            $endTime = isset($contest['contest_end_date'])
                ? CarbonImmutable::parse($contest['contest_end_date'])
                : null;

            return new ContestDTO(
                platform: Platform::CODECHEF,
                platformContestId: $contestId,
                name: $contest['name'] ?? $contestId,
                slug: 'contest-' . $contestId,
                description: $contest['description'] ?? null,
                type: ContestType::CONTEST,
                phase: $contest['status'] ?? 'finished',
                durationSeconds: null,
                startTime: $startTime,
                endTime: $endTime,
                url: $contest['contest_url'] ?? "https://www.codechef.com/$contestId",
                participantCount: null,
                isRated: true,
                tags: [],
                raw: $contest
            );
        });

        // Sync to database
        $syncAction = new SyncPlatformContestsAction();
        $platform = \App\Models\Platform::where('name', '=', 'codechef')->first();

        if (!$platform) {
            $this->error('CodeChef platform not found in database');
            return 1;
        }

        $synced = 0;
        foreach ($dtos as $dto) {
            try {
                // Use reflection to call the protected method
                $method = new \ReflectionMethod($syncAction, 'syncContest');
                $method->setAccessible(true);
                $method->invoke($syncAction, $platform, $dto);
                $synced++;
            } catch (\Exception $e) {
                $this->error("Failed to sync contest {$dto->platformContestId}: " . $e->getMessage());
            }
        }

        $this->info("✓ Synced $synced missing contests from codechef");

        return 0;
    }
}
