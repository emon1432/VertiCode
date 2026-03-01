<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->string('sort')->toString() ?: 'rating_desc';
        $countryId = $request->input('country_id');
        $instituteId = $request->input('institute_id');

        $ratingAggregateQuery = PlatformProfile::query()
            ->selectRaw('user_id, COALESCE(SUM(COALESCE(rating, 0)), 0) as total_rating, COALESCE(SUM(COALESCE(total_solved, 0)), 0) as total_solved')
            ->groupBy('user_id');

        $leaderboardQuery = User::query()
            ->with(['country:id,name,code,flag', 'institute:id,name'])
            ->where('role', 'user')
            ->leftJoinSub($ratingAggregateQuery, 'leaderboard_profiles', function ($join) {
                $join->on('leaderboard_profiles.user_id', '=', 'users.id');
            })
            ->select('users.*')
            ->selectRaw('COALESCE(leaderboard_profiles.total_rating, 0) as total_rating')
            ->selectRaw('COALESCE(leaderboard_profiles.total_solved, 0) as total_solved');

        if ($search = trim($request->input('search', ''))) {
            $leaderboardQuery->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        if ($countryId) {
            $leaderboardQuery->where('users.country_id', $countryId);
        }

        if ($instituteId) {
            $leaderboardQuery->where('users.institute_id', $instituteId);
        }

        if ($platformId = $request->input('platform_id')) {
            $leaderboardQuery->whereExists(function ($query) use ($platformId) {
                $query->selectRaw('1')
                    ->from('platform_profiles')
                    ->whereColumn('platform_profiles.user_id', 'users.id')
                    ->where('platform_profiles.platform_id', $platformId);
            });
        }

        switch ($sort) {
            case 'rating_asc':
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_rating, 0) asc')
                    ->orderBy('users.username');
                break;
            case 'rating_desc':
            default:
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_rating, 0) desc')
                    ->orderBy('users.username');
                break;
            case 'solved_asc':
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_solved, 0) asc')
                    ->orderBy('users.username');
                break;
            case 'solved_desc':
                $leaderboardQuery->orderByRaw('COALESCE(leaderboard_profiles.total_solved, 0) desc')
                    ->orderBy('users.username');
                break;
        }

        $users = $leaderboardQuery->paginate(20)->withQueryString();

        $startRank = ($users->currentPage() - 1) * $users->perPage() + 1;
        $users->getCollection()->transform(function ($user, $index) use ($startRank) {
            $user->leaderboard_rank = $startRank + $index;

            return $user;
        });

        $topUsers = (clone $leaderboardQuery)
            ->limit(3)
            ->get();

        $platforms = Platform::query()
            ->active()
            ->orderBy('display_name')
            ->get(['id', 'name', 'display_name']);

        $selectedCountry = $countryId
            ? Country::query()->find($countryId, ['id', 'name', 'code', 'flag'])
            : null;

        $selectedInstitute = $instituteId
            ? Institute::query()->find($instituteId, ['id', 'name'])
            : null;

        return view('web.pages.leaderboard', [
            'users' => $users,
            'topUsers' => $topUsers,
            'selectedCountry' => $selectedCountry,
            'selectedInstitute' => $selectedInstitute,
            'platforms' => $platforms,
            'sort' => $sort,
        ]);
    }
}
