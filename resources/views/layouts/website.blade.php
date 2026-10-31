<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fffaf5">
    <title>@yield('title', 'RestaurantOS | Restaurant Management Software')</title>
    <meta name="description" content="@yield('description', 'RestaurantOS helps restaurants and cafes manage orders, menus, tables, teams, and branches.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @include('website.partials.navbar')
    <main>
        @yield('content')
    </main>
    @include('website.partials.footer')
    @stack('scripts')
</body>
</html>
