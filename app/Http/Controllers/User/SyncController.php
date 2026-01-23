<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPlatformProfileJob;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    public function sync(PlatformProfile $platformProfile)
    {
        // Authorization: ensure this profile belongs to the user
        if ($platformProfile->user_id !== Auth::id()) {
            abort(403);
        }

        // Dispatch background sync job
        dispatch(
            new SyncPlatformProfileJob(
                $platformProfile,
                CodeforcesAdapter::class
            )
        );

        return back()->with(
            'success',
            'Sync has been queued and will update shortly.'
        );
    }
}
