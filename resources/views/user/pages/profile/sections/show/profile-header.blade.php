<div class="card mb-5 overflow-hidden">
    <!-- Cover Background -->
    <div style="height: 200px; background: var(--primary-gradient); position: relative;">
        <div
            style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1; background-image: url('data:image/svg+xml,%3Csvg width=%2760%27 height=%2760%27 viewBox=%270 0 60 60%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cg fill=%27none%27 fill-rule=%27evenodd%27%3E%3Cg fill=%27%23ffffff%27 fill-opacity=%270.1%27%3E%3Cpath d=%27M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%27/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
        </div>
    </div>

    <!-- Profile Info -->
    <div class="card-body pt-0">
        <div class="row">
            <!-- Avatar -->
            <div class="col-auto">
                <div style="margin-top: -80px; position: relative; z-index: 10;">
                    <div class="rounded-circle border-4 border-white overflow-hidden"
                        style="width: 140px; height: 140px; background: white; display: flex; align-items: center; justify-content: center; box-shadow: var(--card-shadow);">
                        @if ($user->profile_photo_path)
                            <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @elseif($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                                class="w-100 h-100 object-fit-cover">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                                style="background: var(--primary-gradient); font-size: 3rem; color: white; font-weight: 800;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="col">
                <div class="mt-3">
                    <h2 class="mb-1">{{ $user->name }}</h2>
                    <p class="text-muted mb-3" style="font-size: 1.1rem;">@<strong>{{ $user->username }}</strong>
                    </p>

                    @if ($user->bio)
                        <p class="mb-3" style="font-size: 0.95rem; max-width: 600px;">{{ $user->bio }}</p>
                    @endif

                    <!-- Location and Contact -->
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        @if ($user->location)
                            <span class="text-muted">
                                <i class="bi bi-geo-alt"></i> {{ $user->location }}
                            </span>
                        @endif
                        <span class="text-muted">
                            <i class="bi bi-envelope"></i> {{ $user->email }}
                        </span>
                        @if ($user->website)
                            <a href="{{ $user->website }}" target="_blank" class="text-primary text-decoration-none">
                                <i class="bi bi-link-45deg"></i> Website
                            </a>
                        @endif
                    </div>

                    <!-- Social Links -->
                    <div class="d-flex gap-2 mb-4">
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

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        @auth
                            @if (auth()->user()->id === $user->id)
                                @php
                                    $hasActiveProfiles = $user->platformProfiles->where('status', 'Active')->isNotEmpty();
                                    $cooldownMinutes = config('platforms.sync_cooldown_minutes', 120);
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
                                                $cooldownMessage = 'Available in ' . ceil($remainingMinutes) . ' minute' . (ceil($remainingMinutes) > 1 ? 's' : '');
                                            } else {
                                                $hours = floor($remainingMinutes / 60);
                                                $mins = $remainingMinutes % 60;
                                                $cooldownMessage = 'Available in ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
                                                if ($mins > 0) {
                                                    $cooldownMessage .= ' and ' . $mins . ' minute' . ($mins > 1 ? 's' : '');
                                                }
                                            }
                                        }
                                    }

                                    $syncDisabled = !$hasActiveProfiles || !$canSync;
                                @endphp
                                <div class="d-flex gap-2 flex-wrap align-items-center">
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
                                </div>
                                <small class="text-muted d-block mt-2">
                                    @if (!$hasActiveProfiles)
                                        <i class="bi bi-info-circle"></i> Connect a platform to enable sync.
                                    @elseif ($cooldownMessage)
                                        <i class="bi bi-clock-history"></i> {{ $cooldownMessage }} (last synced {{ $user->last_synced_at->diffForHumans() }})
                                    @elseif($user->last_synced_at)
                                        <i class="bi bi-check-circle"></i> Last synced: {{ $user->last_synced_at->diffForHumans() }}
                                    @else
                                        <i class="bi bi-arrow-repeat"></i> Ready to sync
                                    @endif
                                </small>
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
                </div>
            </div>
        </div>
    </div>
</div>
