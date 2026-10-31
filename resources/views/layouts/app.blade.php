
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'RestaurantOS') - Restaurant ERP</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f5f6fa;
            font-family: 'Segoe UI', sans-serif;
        }

        .app-wrapper {
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: #172033;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: .3s;
        }

        .sidebar-brand {
            padding: 23px 20px;
            color: #fff;
            font-size: 21px;
            font-weight: 700;
            border-bottom: 1px solid #293750;
        }

        .sidebar-menu {
            padding: 20px 12px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #aebbd0;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #f97316;
            color: white;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        .top-navbar {
            height: 70px;
            background: #fff;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
        }

        .page-content {
            padding: 25px;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.03);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #fff1e8;
            color: #f97316;
            font-size: 22px;
        }

        @media(max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .page-content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="app-wrapper">

    @include('layouts.sidebar')

    <div class="main-content">

        @include('layouts.navbar')

        <main class="page-content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close"
                        data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>
    </div>

</div>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('show');
    }
</script>
@stack('scripts')
</body>
</html>
