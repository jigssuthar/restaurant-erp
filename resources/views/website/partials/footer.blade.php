
<footer class="site-footer">
    <div class="container">

        <div class="row g-4">

            <div class="col-lg-4">

                <a class="brand text-white" href="{{ route('home') }}">
                    <i class="bi bi-cup-hot-fill text-warning"></i>
                    Restaurant<span>OS</span>
                </a>

                <p class="text-white-50 mt-3">
                    Smart restaurant and café management software
                    designed to simplify your business operations.
                </p>

            </div>

            <div class="col-6 col-lg-2">
                <h2 class="text-white fw-bold mb-3">Company</h2>
                <div class="footer-links">
                    <a href="{{ route('about') }}">About Us</a><br>
                    <a href="{{ route('contact') }}">Contact</a><br>
                    <a href="{{ route('faq') }}">FAQ</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="text-white fw-bold mb-3">Products</h2>
                <div class="footer-links">
                    <a href="{{ route('products.pos') }}">POS Software</a><br>
                    <a href="{{ route('products.inventory') }}">Menu Management</a><br>
                    <a href="{{ route('products.multi-branch') }}">Multi-Branch</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="text-white fw-bold mb-3">Solutions</h2>
                <div class="footer-links">
                    <a href="{{ route('solutions.restaurants') }}">Restaurants</a><br>
                    <a href="{{ route('solutions.cafes') }}">Cafés</a><br>
                    <a href="{{ route('solutions.cloud-kitchens') }}">Cloud Kitchens</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="text-white fw-bold mb-3">Explore</h2>
                <div class="footer-links">
                    <a href="{{ route('privacy') }}">Privacy Policy</a><br>
                    <a href="{{ route('terms') }}">Terms</a><br>
                    <a href="{{ route('pricing') }}">Pricing</a>
                </div>
            </div>

        </div>

        <hr class="border-secondary my-4">

        <div class="footer-bottom d-flex justify-content-between flex-wrap gap-2 pt-4">
            <small>© {{ date('Y') }} RestaurantOS. All rights reserved.</small>
            <small>Restaurant & Café Management Platform</small>
        </div>

    </div>
</footer>
