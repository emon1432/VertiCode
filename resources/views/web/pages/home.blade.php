@extends('web.layouts.app')
@section('content')
    <!-- Hero Section -->
    <div class="hero-section">

        <!-- Hero Content -->
        <div class="container position-relative" style="padding-top: 120px; padding-bottom: 80px;">
            <div class="row align-items-center">
                <div class="col-lg-6 text-white mb-5 mb-lg-0">
                    <h1 class="display-3 fw-bold mb-4">
                        Track Your <span style="color: #ffd700;">Problem-Solving</span> Journey
                    </h1>
                    <p class="lead mb-4" style="font-size: 1.25rem; opacity: 0.95;">
                        Monitor your problem counts, ratings, contest performance, and rankings across all major
                        competitive programming platforms in one unified dashboard.
                    </p>

                    <div class="d-flex flex-wrap gap-3 mb-5">
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <a href="{{ route('dashboard') }}" class="btn btn-primary-gradient btn-lg">
                                    <i class="bi bi-speedometer2"></i> Go to Dashboard
                                </a>
                            @else
                                <a href="{{ route('user.profile.show', ['username' => auth()->user()->username]) }}"
                                    class="btn btn-primary-gradient btn-lg">
                                    <i class="bi bi-person-circle"></i>
                                    View Profile
                                </a>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="btn btn-primary-gradient btn-lg">
                                <i class="bi bi-rocket-takeoff"></i> Start Your Journey
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light-custom btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </a>
                        @endauth
                    </div>

                    <!-- Quick Stats -->
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="stats-card">
                                <div class="stat-label">Problems Solved</div>
                                <div class="stat-value">5000+</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stats-card">
                                <div class="stat-label">Active Users</div>
                                <div class="stat-value">10k+</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="stats-card">
                                <div class="stat-label">Platforms</div>
                                <div class="stat-value">5+</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Preview -->
                <div class="col-lg-6">
                    <div class="stats-window floating-animation">
                        <!-- User Profile Card -->
                        <div
                            style="background: rgba(255, 255, 255, 0.1); border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div
                                    style="width: 40px; height: 40px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                    👤
                                </div>
                                <div>
                                    <div style="color: white; font-weight: 700;">John Doe</div>
                                    <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.85rem;">Rank: #47</div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Overview -->
                        <div style="margin-bottom: 15px;">
                            <div style="color: rgba(255, 255, 255, 0.8); font-weight: 600; margin-bottom: 10px;">Overall
                                Statistics</div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div class="stat-item"
                                    style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                    <div class="stat-label">Total Problems</div>
                                    <div class="stat-value" style="font-size: 1.5rem;">287</div>
                                </div>
                                <div class="stat-item"
                                    style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                    <div class="stat-label">Current Rating</div>
                                    <div class="stat-value" style="font-size: 1.5rem;">1850</div>
                                </div>
                                <div class="stat-item"
                                    style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                    <div class="stat-label">Best Rating</div>
                                    <div class="stat-value" style="font-size: 1.5rem;">2140</div>
                                </div>
                                <div class="stat-item"
                                    style="background: rgba(255, 255, 255, 0.08); border-radius: 8px; padding: 10px;">
                                    <div class="stat-label">Contests</div>
                                    <div class="stat-value" style="font-size: 1.5rem;">43</div>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Breakdown -->
                        <div>
                            <div style="color: rgba(255, 255, 255, 0.8); font-weight: 600; margin-bottom: 10px;">
                                Platform Statistics</div>
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                                    <span class="platform-badge" style="margin: 0;"><i class="bi bi-circle-fill"
                                            style="color: #1f77d2;"></i> Codeforces</span>
                                    <span style="color: white; font-weight: 600;">156 problems</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                                    <span class="platform-badge" style="margin: 0;"><i class="bi bi-circle-fill"
                                            style="color: #ffc116;"></i> LeetCode</span>
                                    <span style="color: white; font-weight: 600;">89 problems</span>
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: rgba(255, 255, 255, 0.05); border-radius: 6px;">
                                    <span class="platform-badge" style="margin: 0;"><i class="bi bi-circle-fill"
                                            style="color: #5cb85c;"></i> CodeChef</span>
                                    <span style="color: white; font-weight: 600;">42 problems</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="feature-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Powerful Features to Boost Your Competitive Programming</h2>
                <p class="lead text-muted">Everything you need to monitor and improve your problem-solving performance
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Auto Profile Sync</h4>
                        <p class="text-muted">Automatically sync your accounts from Codeforces, LeetCode, CodeChef,
                            HackerRank, and more. Your stats update in real-time.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto" style="background: var(--secondary-gradient);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Detailed Analytics</h4>
                        <p class="text-muted">View problem counts, ratings, best ratings, contest performance, and
                            difficulty distribution across all platforms.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto" style="background: var(--dark-gradient);">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Global Leaderboard</h4>
                        <p class="text-muted">Compete on the global leaderboard. Filter by country, rating range, or
                            recent activity. See where you stand.</p>
                    </div>
                </div>
            </div>

            <!-- Additional Features -->
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto"
                            style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Friends & Comparisons</h4>
                        <p class="text-muted">Add friends, compare stats, and view detailed profiles. Track progress
                            with your competitive community.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto"
                            style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Contest Tracking</h4>
                        <p class="text-muted">Monitor upcoming contests across platforms, track your performance, and
                            maintain your contest statistics.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon mx-auto"
                            style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Privacy & Security</h4>
                        <p class="text-muted">Your account is secure. Choose what to display publicly, keep your
                            profile private, or share selectively with friends.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-5" style="background: var(--primary-gradient);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-white mb-4 mb-lg-0">
                    <h2 class="fw-bold mb-2">Start Tracking Your Progress Today</h2>
                    <p class="mb-0 opacity-75">Join thousands of competitive programmers already using VertiCode to
                        monitor their journey and compete on global leaderboards.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    @auth
                        <a href="{{ route('user.profile.show', ['username' => auth()->user()->username]) }}"
                            class="btn btn-light btn-lg" style="border-radius: 50px; padding: 12px 40px; font-weight: 600;">
                            View Profile <i class="bi bi-arrow-right"></i>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg"
                            style="border-radius: 50px; padding: 12px 40px; font-weight: 600;">
                            Sign Up Free <i class="bi bi-arrow-right"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
