<?php

namespace App\Http\Controllers\Admin;

use App\Actions\SyncPlatformContestsAction;
use App\Actions\SyncPlatformProblemsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformRequest;
use App\Jobs\SyncPlatformContestsJob;
use App\Jobs\SyncPlatformProblemsJob;
use App\Models\Platform;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Services\PlatformSyncService;
use App\View\Components\Actions;
use App\View\Components\PlatformInfo;
use App\View\Components\StatusBadge;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
        }
        return view('admin.pages.platforms.index');
    }

    public function create()
    {
        return view('admin.pages.platforms.create');
    }

    public function store(PlatformRequest $request)
    {
        try {
            Platform::create($request->except(['_token', 'image']) + [
                'image' => $request->file('image') ? imageUploadManager($request->file('image'), $request->name, 'platforms') : null,
            ]);
            return response()->json([
                'status' => 200,
                'message' => __('Platform created successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Platform $platform)
    {
        return view('admin.pages.platforms.edit', compact('platform'));
    }

    public function update(PlatformRequest $request, Platform $platform)
    {
        try {
            $platform->update($request->except(['_token', 'image']) + [
                'image' => $request->file('image') ? imageUpdateManager($request->file('image'), $request->name, 'platforms', $platform->image) : $platform->image,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('Platform updated successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function destroy(Platform $platform)
    {
        try {
            imageDeleteManager($platform->image);
            $platform->delete();

            return response()->json([
                'status' => 200,
                'message' => __('Platform deleted successfully'),
                'redirect' => route('platforms.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    /**
     * Sync contests for a specific platform
     */
    public function syncContests(Request $request, Platform $platform)
    {
        try {
            // Check platform status
            if ($platform->status !== 'Active') {
                return response()->json([
                    'status' => 400,
                    'message' => __('Platform is not active'),
                ], 400);
            }

            // Get platform adapter
            $adapterClass = $this->getAdapterClass($platform->name);

            if (!$adapterClass) {
                return response()->json([
                    'status' => 400,
                    'message' => __('No adapter found for this platform'),
                ], 400);
            }

            // Check if platform supports contests
            $adapter = app($adapterClass);
            if (!method_exists($adapter, 'supportsContests') || !$adapter->supportsContests()) {
                return response()->json([
                    'status' => 400,
                    'message' => __('This platform does not support contest syncing'),
                ], 400);
            }

            // Check if sync is queued or dispatch
            $dispatchAsync = $request->get('async', false);

            if ($dispatchAsync) {
                // Dispatch job to queue
                SyncPlatformContestsJob::dispatch($platform->id, $adapterClass);

                return response()->json([
                    'status' => 200,
                    'message' => __('Contest sync has been queued and will run in the background'),
                ]);
            } else {
                // Execute sync synchronously
                $action = app(SyncPlatformContestsAction::class);
                $result = $action->execute($platform, $adapter, 100);

                if ($result['success']) {
                    return response()->json([
                        'status' => 200,
                        'message' => $result['message'],
                        'data' => [
                            'synced' => $result['synced'],
                            'total' => $platform->contests()->count(),
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => $result['message'],
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Sync failed: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync problems for a specific platform
     */
    public function syncProblems(Request $request, Platform $platform)
    {
        try {
            // Check platform status
            if ($platform->status !== 'Active') {
                return response()->json([
                    'status' => 400,
                    'message' => __('Platform is not active'),
                ], 400);
            }

            // Get platform adapter
            $adapterClass = $this->getAdapterClass($platform->name);

            if (!$adapterClass) {
                return response()->json([
                    'status' => 400,
                    'message' => __('No adapter found for this platform'),
                ], 400);
            }

            // Check if platform supports problems
            $adapter = app($adapterClass);
            if (!method_exists($adapter, 'supportsProblems') || !$adapter->supportsProblems()) {
                return response()->json([
                    'status' => 400,
                    'message' => __('This platform does not support problem syncing'),
                ], 400);
            }

            // Check if sync is queued or dispatch
            $dispatchAsync = $request->get('async', false);
            $contestId = $request->get('contest_id');

            if ($dispatchAsync) {
                // Dispatch job to queue
                SyncPlatformProblemsJob::dispatch($platform->id, $adapterClass, $contestId);

                return response()->json([
                    'status' => 200,
                    'message' => __('Problem sync has been queued and will run in the background'),
                ]);
            } else {
                // Execute sync synchronously
                $action = app(SyncPlatformProblemsAction::class);
                $result = $action->execute($platform, $adapter, $contestId);

                if ($result['success']) {
                    return response()->json([
                        'status' => 200,
                        'message' => $result['message'],
                        'data' => [
                            'synced' => $result['synced'],
                            'total' => $platform->problems()->count(),
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => 500,
                        'message' => $result['message'],
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Sync failed: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync all platforms contests and/or problems
     */
    public function syncAll(Request $request, PlatformSyncService $syncService)
    {
        try {
            $type = $request->get('type', 'all'); // all, contests, problems
            $force = $request->boolean('force', false);

            $results = match($type) {
                'contests' => $syncService->dispatchContestSyncs(!$force),
                'problems' => $syncService->dispatchProblemSyncs(!$force),
                default => $syncService->dispatchAllSyncs(!$force),
            };

            $dispatched = $results->where('status', 'dispatched')->count();
            $skipped = $results->where('status', 'skipped')->count();
            $failed = $results->where('status', 'failed')->count();

            return response()->json([
                'status' => 200,
                'message' => __("Sync jobs dispatched: {$dispatched}, Skipped: {$skipped}, Failed: {$failed}"),
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Sync failed: ') . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get adapter class for platform
     */
    private function getAdapterClass(string $platformName): ?string
    {
        $adapters = [
            'atcoder' => AtCoderAdapter::class,
            // Add other platform adapters here as they are implemented
            // 'codeforces' => CodeforcesAdapter::class,
            // 'leetcode' => LeetCodeAdapter::class,
            // ...
        ];

        return $adapters[strtolower($platformName)] ?? null;
    }

    protected function data()
    {
        return Platform::orderBy('display_name', 'asc')->get()->map(function ($platform) {
            $platform->actions = (new Actions([
                'model' => $platform,
                'resource' => 'platforms',
                'buttons' => [
                    'basic' => [
                        'view' => true,
                        'edit' => true,
                        'delete' => true,
                    ],
                ],
            ]))->render()->render();
            $platform->name = (new PlatformInfo($platform))->render()->render();
            $platform->base_url = '<a href="' . e($platform->base_url) . '" target="_blank">' . e($platform->base_url) . '</a>';
            $platform->status = (new StatusBadge($platform->status))->render()->render();
            return $platform;
        })->toArray();
    }
}
