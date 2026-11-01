<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/dinecentral-mark.svg') }}">
    <title>@yield('title', 'Restaurant Operations') | DineCentral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-light">
    <header class="bg-white border-bottom">
        <div class="container-fluid px-3 px-lg-4 py-3 d-flex align-items-center justify-content-between gap-3">
            <a href="{{ request()->routeIs('kitchen-display.*') ? route('kitchen-display.index') : route('service-app.index') }}"
               class="operations-brand text-decoration-none fw-bold text-dark fs-5">
                <img class="brand-mark" src="{{ asset('images/dinecentral-mark.svg') }}" alt="">
                <span>Dine<span>Central</span></span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i>Log out
                    </button>
                </form>
            </div>
        </div>
    </header>
    <main class="container-fluid px-3 px-lg-4 py-4">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
