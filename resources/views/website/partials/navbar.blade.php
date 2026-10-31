
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
    <div class="container">

        <a class="brand" href="{{ route('home') }}">
            <i class="bi bi-cup-hot-fill text-orange"></i>
            Restaurant<span>OS</span>
        </a>

       <button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNavbar"
        aria-controls="mainNavbar"
        aria-expanded="false"
        aria-label="Toggle navigation">

    <span class="navbar-toggler-icon"></span>

</button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav mx-auto gap-lg-2">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                       href="{{ route('home') }}">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('features') ? 'active' : '' }}" href="{{ route('features') }}">Features</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('solutions.*') ? 'active' : '' }}" href="#"
                       data-bs-toggle="dropdown" role="button" aria-expanded="false">Solutions</a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('solutions.restaurants') }}">Restaurants</a></li>
                        <li><a class="dropdown-item" href="{{ route('solutions.cafes') }}">Cafés</a></li>
                        <li><a class="dropdown-item" href="{{ route('solutions.cloud-kitchens') }}">Cloud Kitchens</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('products.*') ? 'active' : '' }}" href="#"
                       data-bs-toggle="dropdown" role="button" aria-expanded="false">Products</a>

                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('products.pos') }}">POS Software</a></li>
                        <li><a class="dropdown-item" href="{{ route('products.inventory') }}">Inventory</a></li>
                        <li><a class="dropdown-item" href="{{ route('products.multi-branch') }}">Multi-Branch ERP</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Pricing</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                </li>

            </ul>

            <div class="d-flex gap-2 mt-3 mt-lg-0">

                <a href="{{ route('login') }}" class="btn btn-outline-orange">Login</a>

                <a href="{{ route('register') }}"
                   class="btn btn-orange">Get Started</a>

            </div>

        </div>
    </div>
</nav>
