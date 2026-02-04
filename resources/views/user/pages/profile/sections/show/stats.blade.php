<div class="row mb-5">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div
                    style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    {{ $user->platformProfiles->count() }}
                </div>
                <p class="text-muted mb-0 mt-2">Connected Platforms</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div
                    style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    {{ $user->platformProfiles->sum('total_solved') }}
                </div>
                <p class="text-muted mb-0 mt-2">Problems Solved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 1.2rem; font-weight: 600; color: var(--primary-color);">
                    {{ $user->created_at?->format('M Y') ?? 'N/A' }}
                </div>
                <p class="text-muted mb-0 mt-2">Member Since</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 1.2rem; font-weight: 600; color: var(--primary-color);">
                    {{ $user->email_verified_at ? '✓ Verified' : 'Not Verified' }}
                </div>
                <p class="text-muted mb-0 mt-2">Email Status</p>
            </div>
        </div>
    </div>
</div>
