
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/dinecentral-mark.svg') }}">

    <title>@yield('title') | DineCentral</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    <style>
        body {
            min-height: 100vh;
            background: #f4f5f9;
            font-family: 'Segoe UI', sans-serif;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,.07);
        }

        .auth-logo {
            color: #f97316;
            font-size: 27px;
            font-weight: 800;
        }

        .auth-btn {
            background: #f97316;
            border: none;
            color: white;
            padding: 12px;
        }

        .auth-btn:hover {
            background: #ea580c;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">

        <div class="w-100 d-flex flex-column align-items-center">

            <div class="mb-4 auth-logo">
                <img class="brand-mark" src="{{ asset('images/dinecentral-mark.svg') }}" alt="">
                <span>Dine<span>Central</span></span>
            </div>

            @if(session('success'))
                <div class="alert alert-success w-100" style="max-width:440px">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success w-100" style="max-width:440px">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')

            <p class="text-muted small mt-4">
                © {{ date('Y') }} DineCentral. All rights reserved.
            </p>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
