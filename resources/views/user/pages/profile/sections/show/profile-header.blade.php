<div class="card mb-5 overflow-hidden profile-header">
    <div class="profile-cover">
        <p class="text-center h-100 d-flex flex-column justify-content-center align-items-center">
            <span class="mt-2 text-white" style="font-size: 1.25rem;">{{ $user->fav_quote }}</span>
        </p>
        <div class="profile-cover-pattern"></div>
    </div>

    <div class="card-body pt-0">
        <div class="row g-4 align-items-start">
            <div class="col-auto">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar rounded-circle border-4 border-white overflow-hidden">
                        @if ($user->image && imageExists($user->image))
                            <img src="{{ imageShow($user->image) }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @elseif($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @else
                            <div
                                class="w-100 h-100 d-flex align-items-center justify-content-center profile-avatar-fallback">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="mt-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2 justify-content-between">
                        <h2 class="mb-0">{{ $user->name }}</h2>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($user->website)
                                <a href="https://{{ $user->website }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="Website">
                                    <i class="bi bi-globe"></i>
                                </a>
                            @endif
                            @if ($user->facebook)
                                <a href="https://facebook.com/{{ $user->facebook }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                            @endif
                            @if ($user->instagram)
                                <a href="https://instagram.com/{{ $user->instagram }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            @endif
                            @if ($user->twitter)
                                <a href="https://twitter.com/{{ $user->twitter }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                            @endif
                            @if ($user->github)
                                <a href="https://github.com/{{ $user->github }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="GitHub">
                                    <i class="bi bi-github"></i>
                                </a>
                            @endif
                            @if ($user->linkedin)
                                <a href="https://linkedin.com/in/{{ $user->linkedin }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary rounded-circle" title="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                    <p class="text-muted mb-3 profile-username">@<strong>{{ $user->username }}</strong></p>
                    



                    <div class="d-flex gap-2 flex-wrap">
                        @auth
                            @if (auth()->user()->id === $user->id)
                                @php
                                    $hasActiveProfiles = $user->platformProfiles
                                        ->where('status', 'Active')
                                        ->isNotEmpty();
                                    $cooldownMinutes = (int) config('platforms.sync_cooldown_minutes', 120);
                                    $canSync = true;
                                    $cooldownMessage = '';

                                    if ($user->last_synced_at) {
                                        $nextAvailableAt = $user->last_synced_at->copy()->addMinutes($cooldownMinutes);
                                        if (now()->lt($nextAvailableAt)) {
                                            $canSync = false;
                                            $remainingMinutes = now()->diffInMinutes($nextAvailableAt, false);

                                            if ($remainingMinutes < 1) {
                                                $cooldownMessage = 'Available in less than a minute';
                                            } elseif ($remainingMinutes < 60) {
                                                $cooldownMessage =
                                                    'Available in ' .
                                                    ceil($remainingMinutes) .
                                                    ' minute' .
                                                    (ceil($remainingMinutes) > 1 ? 's' : '');
                                            } else {
                                                $hours = floor($remainingMinutes / 60);
                                                $mins = $remainingMinutes % 60;
                                                $cooldownMessage =
                                                    'Available in ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
                                                if ($mins > 0) {
                                                    $cooldownMessage .=
                                                        ' and ' . $mins . ' minute' . ($mins > 1 ? 's' : '');
                                                }
                                            }
                                        }
                                    }

                                    $syncDisabled = !$hasActiveProfiles || !$canSync;
                                @endphp
                                <a href="{{ route('user.profile.edit', $user->username) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Profile
                                </a>
                                <form method="POST" action="{{ route('user.sync') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary"
                                        {{ $syncDisabled ? 'disabled' : '' }}
                                        title="{{ $cooldownMessage ? $cooldownMessage : 'Sync all connected platforms' }}">
                                        <i class="bi bi-arrow-repeat"></i> Sync Profiles
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-primary" onclick="alert('Message feature coming soon')">
                                    <i class="bi bi-chat-dots"></i> Message
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right"></i> Login to Message
                            </a>
                        @endauth
                    </div>

                    @auth
                        @if (auth()->user()->id === $user->id)
                            <small class="text-muted d-block mt-2">
                                @if (!$hasActiveProfiles)
                                    <i class="bi bi-info-circle"></i> Connect a platform to enable sync.
                                @elseif ($cooldownMessage)
                                    <i class="bi bi-clock-history"></i> {{ $cooldownMessage }} (last synced
                                    {{ $user->last_synced_at->diffForHumans() }})
                                @elseif($user->last_synced_at)
                                    <i class="bi bi-check-circle"></i> Last synced:
                                    {{ $user->last_synced_at->diffForHumans() }}
                                @else
                                    <i class="bi bi-arrow-repeat"></i> Ready to sync
                                @endif
                            </small>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
