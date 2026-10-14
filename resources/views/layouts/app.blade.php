<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Dashboard') | Chicago Chicken City</title>

    <link rel="icon" type="image/png" href="{{ asset('images/chicago-logo-transparent.png') }}">
    <link rel="stylesheet" href="{{ asset('css/ccc-app.css') }}?v={{ filemtime(public_path('css/ccc-app.css')) }}">
    @stack('styles')

    <script src="{{ asset('js/auth-history.js') }}" defer></script>
    <script src="{{ asset('js/ccc-app.js') }}?v={{ filemtime(public_path('js/ccc-app.js')) }}" defer></script>
</head>
<body class="ccc-app">

    <a href="#main-content" class="ccc-skip-link">Skip to main content</a>

    @include('partials.app-topbar')

    <div class="ccc-shell">
        @include('partials.app-sidebar')

        <div class="ccc-backdrop" data-nav-backdrop data-nav-close hidden></div>

        <main id="main-content" class="ccc-main" tabindex="-1">
            <div class="ccc-container">
                @include('partials.flash-messages')

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
