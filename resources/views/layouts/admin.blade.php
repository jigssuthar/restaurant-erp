
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/dinecentral-mark.svg') }}">

    <title>@yield('title', 'Admin Dashboard') | DineCentral</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8fafc;
            font-family: Arial, sans-serif;
            overflow-x: hidden;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */

        .admin-sidebar {
            width: 260px;
            min-width: 260px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e9edf2;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
            overflow-y: auto;
            transition: transform .3s ease;
        }

        .sidebar-brand {
            height: 75px;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 24px;
            border-bottom: 1px solid #edf0f4;
            font-size: 21px;
            font-weight: 800;
            color: #f97316;
        }

        .sidebar-menu {
            padding: 20px 14px;
        }

        .sidebar-heading {
            color: #94a3b8;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 20px 12px 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            margin-bottom: 5px;
            border-radius: 8px;
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            transition: .2s;
        }

        /* Only individual sidebar link gets hover */

        .sidebar-link:hover {
            background: #fff3e9;
            color: #f97316;
        }

        .sidebar-link.active {
            background: #fff0e5;
            color: #ea580c;
            font-weight: 700;
        }

        .sidebar-link i {
            font-size: 18px;
        }

        /* Main content */

        .admin-main {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-width: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .3s ease, width .3s ease;
        }

        .admin-header {
            height: 75px;
            min-height: 75px;
            background: #ffffff;
            border-bottom: 1px solid #e9edf2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .admin-content {
            padding: 28px;
            flex: 1;
            min-width: 0;
        }

        .admin-card,
        .admin-table-card {
            background: #ffffff;
            border: 1px solid #edf0f4;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .btn-orange {
            background: #f97316;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
        }

        .btn-orange:hover {
            background: #ea580c;
            color: white;
        }

        /* Mobile overlay */

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,.45);
            z-index: 1040;
        }

        .sidebar-toggle {
            border: 0;
            background: #fff3e9;
            color: #f97316;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        /* Tablet and mobile */

        @media(max-width: 991px) {

            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .admin-content {
                padding: 20px;
            }

        }

        @media(max-width: 576px) {

            .admin-header {
                padding: 0 14px;
                height: 65px;
                min-height: 65px;
            }

            .admin-content {
                padding: 14px;
            }

            .admin-card,
            .admin-table-card {
                padding: 15px;
            }

        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    @include('admin.partials.sidebar')

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="admin-main">

        @include('admin.partials.header')

        <div class="admin-content">

            @yield('content')

        </div>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    sidebar?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 991) {
                closeSidebar();
            }
        });
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });

});
</script>

@stack('scripts')

</body>
</html>
