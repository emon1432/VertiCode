<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>{{ config('app.name', 'VertiCode') }} - Track Your Problem Solving Journey</title>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --dark-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
        }

        .btn-primary-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }

        .btn-outline-light-custom {
            border: 2px solid white;
            color: white;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 50px;
            transition: all 0.3s ease;
            background: transparent;
        }

        .btn-outline-light-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #fff;
        }

        .platform-badge {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            color: white;
            font-weight: 500;
            display: inline-block;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .platform-badge:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }

        .stats-window {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .feature-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--primary-gradient);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .feature-box {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.2);
        }

        .stat-item {
            text-align: center;
            padding: 15px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-value {
            color: white;
            font-size: 1.8rem;
            font-weight: 800;
            margin: 5px 0;
        }

        .leaderboard-preview {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .lb-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
        }

        .lb-rank {
            font-weight: 700;
            color: #ffd700;
            min-width: 30px;
        }
    </style>
</head>

<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark position-relative">
            <div class="container">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <i class="bi bi-code-slash"></i> VertiCode
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        @auth
                            @if (auth()->user()->role === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                            @else
                                <li class="nav-item ms-3">
                                    <a href="{{ route('user.profile', ['username' => auth()->user()->username]) }}"
                                        class="btn btn-primary-gradient">
                                        <i class="bi bi-person-circle"></i> Profile
                                    </a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Login</a>
                            </li>
                            <li class="nav-item ms-3">
                                <a href="{{ route('register') }}" class="btn btn-outline-light-custom">
                                    Get Started
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="container position-relative" style="padding-top: 80px; padding-bottom: 80px;">
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
                                <a href="{{ route('user.profile', ['username' => auth()->user()->username]) }}"
                                    class="btn btn-primary-gradient btn-lg">
                                    <i class="bi bi-rocket-takeoff"></i> View Profile
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
                        <a href="{{ route('user.profile', ['username' => auth()->user()->username]) }}"
                            class="btn btn-light btn-lg"
                            style="border-radius: 50px; padding: 12px 40px; font-weight: 600;">
                            View Dashboard <i class="bi bi-arrow-right"></i>
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

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy; {{ date('Y') }} VertiCode. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white text-decoration-none me-3"><i class="bi bi-github"></i>
                        GitHub</a>
                    <a href="#" class="text-white text-decoration-none me-3"><i class="bi bi-twitter"></i>
                        Twitter</a>
                    <a href="#" class="text-white text-decoration-none"><i class="bi bi-linkedin"></i>
                        LinkedIn</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
</body>

</html>
