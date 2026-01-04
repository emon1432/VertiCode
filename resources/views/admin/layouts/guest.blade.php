<!DOCTYPE html>
<html lang="en" class="layout-wide customizer-hide" dir="ltr" data-skin="default" data-assets-path="/admin/"
    data-template="vertical-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') | {{ settings('business_settings', 'company_name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ imageShow(settings('business_settings', 'favicon')) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/fonts/iconify-icons.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/node-waves/node-waves.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/css/core.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/css/demo.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/libs/@form-validation/form-validation.css">
    <link rel="stylesheet" href="{{ asset('admin') }}/vendor/css/pages/page-auth.css">
    <script src="{{ asset('admin') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('admin') }}/js/config.js"></script>
</head>

<body style="--bs-scrollbar-width: 0px;">
    <div class="authentication-wrapper authentication-cover">
        <a href="{{ route('home') }}" class="app-brand auth-cover-brand">
            <span class="app-brand-logo demo">
                <span class="text-primary">
                    <img src="{{ imageShow(settings('business_settings', 'logo')) }}"
                        alt="{{ settings('business_settings', 'company_name') }}" class="logo"
                        style="max-height: 40px; max-width: 100%;">
                </span>
            </span>
            <span class="app-brand-text demo text-heading fw-bold">
                {{ settings('business_settings', 'company_name') }}
            </span>
        </a>
        <div class="authentication-inner row m-0">
            @yield('content')
        </div>
    </div>

    <script src="{{ asset('admin') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('admin') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@algolia/autocomplete-js.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/i18n/i18n.js"></script>
    <script src="{{ asset('admin') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('admin') }}/vendor/libs/@form-validation/auto-focus.js"></script>
    <script src="{{ asset('admin') }}/js/main.js"></script>
    <script src="{{ asset('admin') }}/js/pages-auth.js"></script>
</body>

</html>
