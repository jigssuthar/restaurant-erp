
<aside class="sidebar">

    <div class="sidebar-brand">
        <img class="brand-mark" src="{{ asset('images/dinecentral-mark.svg') }}" alt="">
        <span>Dine<span class="text-warning">Central</span></span>
    </div>

    <div class="sidebar-menu">

        <small class="text-secondary px-3">MAIN MENU</small>

        <a href="{{ route('admin.dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>

        <a href="#">
            <i class="bi bi-shop"></i>
            Branches
        </a>

        <a href="#">
            <i class="bi bi-receipt"></i>
            POS Billing
        </a>

        <a href="#">
            <i class="bi bi-cart3"></i>
            Orders
        </a>

        <a href="#">
            <i class="bi bi-fire"></i>
            Kitchen / KOT
        </a>

        <a href="#">
            <i class="bi bi-grid-3x3"></i>
            Table Management
        </a>

        <hr class="border-secondary">

        <small class="text-secondary px-3">MANAGEMENT</small>

        <a href="#">
            <i class="bi bi-journal-text"></i>
            Categories
        </a>

        <a href="#">
            <i class="bi bi-cup-straw"></i>
            Products / Menu
        </a>

        <a href="#">
            <i class="bi bi-box-seam"></i>
            Inventory
        </a>

        <a href="#">
            <i class="bi bi-truck"></i>
            Suppliers
        </a>

        <a href="#">
            <i class="bi bi-wallet2"></i>
            Expenses
        </a>

        <a href="#">
            <i class="bi bi-people"></i>
            Staff Management
        </a>

        <hr class="border-secondary">

        <small class="text-secondary px-3">REPORTS & SETTINGS</small>

        <a href="#">
            <i class="bi bi-bar-chart"></i>
            Reports
        </a>

        <a href="#">
            <i class="bi bi-gear"></i>
            Settings
        </a>

    </div>

    <div class="position-absolute bottom-0 w-100 p-3 border-top border-secondary">
        <div class="text-white small">
            <i class="bi bi-shield-check text-success"></i>
            DineCentral v1.0
        </div>
    </div>

</aside>
