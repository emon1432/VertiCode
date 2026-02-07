@php
    // Calculate total solved problems across all platforms
    $totalSolved = $user->platformProfiles()->sum('total_solved');

    // Calculate total rating (sum of all platform ratings)
    $totalRating = $user->platformProfiles()->sum('rating');

    // Calculate global rank based on total rating
    $userRatings = \App\Models\User::query()
        ->select('users.id')
        ->where('users.role', 'user')
        ->selectRaw('COALESCE(SUM(platform_profiles.rating), 0) as total_rating')
        ->leftJoin('platform_profiles', 'users.id', '=', 'platform_profiles.user_id')
        ->groupBy('users.id')
        ->orderByDesc('total_rating')
        ->pluck('id');

    $globalRank = $userRatings->search($user->id) + 1;

    // Calculate unsolved problems (attempted but not solved)
    // Note: This assumes you have a 'tried' or 'attempted' count field
    // If not available, you may need to add this field to platform_profiles table
    $unsolvedProblems = 0; // Placeholder - update when you have the data field
@endphp

<div class="row mb-5 g-3">
    <!-- Global Rank -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm transition" style="transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="text-muted small mb-1">GLOBAL RANK</p>
                        <h3 class="mb-0"
                            style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">
                            #{{ $globalRank }}
                        </h3>
                    </div>
                    <div style="font-size: 2rem; color: #fa709a; opacity: 0.3;">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                </div>
                <small class="text-muted">based on total rating</small>
            </div>
        </div>
    </div>

    <!-- Total Rating -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm transition" style="transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="text-muted small mb-1">TOTAL RATING</p>
                        <h3 class="mb-0"
                            style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">
                            {{ $totalRating }}
                        </h3>
                    </div>
                    <div style="font-size: 2rem; color: #38f9d7; opacity: 0.3;">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
                <small class="text-muted">sum of all platforms</small>
            </div>
        </div>
    </div>

    <!-- Total Problems Solved -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm transition" style="transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="text-muted small mb-1">TOTAL PROBLEMS SOLVED</p>
                        <h3 class="mb-0"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">
                            {{ $totalSolved }}
                        </h3>
                    </div>
                    <div style="font-size: 2rem; color: #667eea; opacity: 0.3;">
                        <i class="bi bi-code-square"></i>
                    </div>
                </div>
                <small class="text-muted">across all platforms</small>
            </div>
        </div>
    </div>

    <!-- Unsolved Problems -->
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm transition" style="transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="text-muted small mb-1">UNSOLVED PROBLEMS</p>
                        <h3 class="mb-0"
                            style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 700;">
                            {{ $unsolvedProblems }}
                        </h3>
                    </div>
                    <div style="font-size: 2rem; color: #f5576c; opacity: 0.3;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <small class="text-muted">attempted but failed</small>
            </div>
        </div>
    </div>
</div>
