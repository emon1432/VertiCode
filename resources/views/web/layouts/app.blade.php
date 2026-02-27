<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="title" content="@yield('title', config('app.name', 'VertiCode') . ' - Track Your Problem Solving Journey')">
    <meta name="description" content="@yield('description', 'VertiCode is a platform to track your problem-solving journey, connect with the community, and participate in contests.')">
    <meta name="keywords" content="competitive programming, coding challenges, problem-solving, contests, leaderboard, community">
    <meta name="author" content="VertiCode Team">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <title>@yield('title', config('app.name', 'VertiCode') . ' - Track Your Problem Solving Journey')</title>
    <link rel="stylesheet" href="{{ asset('web/css/style-web.css') }}?v={{ time() }}">
    @stack('styles')
</head>

<body>
    @include('web.layouts.includes.navbar')

    @yield('content')

    @include('web.layouts.includes.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
