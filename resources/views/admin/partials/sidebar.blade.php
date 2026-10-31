
<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-brand">
        <i class="bi bi-shop me-2"></i>
        RestaurantOS
    </div>

    <div class="sidebar-menu">

        <div class="sidebar-heading">MAIN MENU</div>

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>

        <div class="sidebar-heading">BUSINESS MANAGEMENT</div>

        @if(auth()->user()->isPlatformAdmin())
            <a href="{{ route('admin.organizations.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                Organizations
            </a>
        @endif
        @if(auth()->user()->hasAnyRole('admin'))
            <a href="{{ route('admin.staff.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i>
                Staff Management
            </a>
        @endif
        @if(auth()->user()->isPlatformAdmin())
            <a href="{{ route('admin.saas-plans.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.saas-plans.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i>
                Subscription Plans
            </a>
        @endif

        @if(auth()->user()->hasAnyRole('admin', 'manager'))
            <a href="{{ route('admin.branches.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.branches.*') ? 'active' : '' }}">
                <i class="bi bi-shop-window"></i>
                Branches
            </a>
        @endif

        <div class="sidebar-heading">RESTAURANT MANAGEMENT</div>

        <a href="{{ route('admin.categories.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3"></i>
            Categories
        </a>

        <a href="{{ route('admin.products.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-cup-hot"></i>
            Products & Menu
        </a>

        <a href="{{ route('admin.dining-tables.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.dining-tables.*') ? 'active' : '' }}">
            <i class="bi bi-table"></i>
            Dining Tables
        </a>

        <a href="{{ route('admin.orders.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            Orders
        </a>

        <div class="sidebar-heading">SETTINGS</div>

        @if(auth()->user()->hasAnyRole('admin') && !auth()->user()->isPlatformAdmin())
            <a href="{{ route('admin.billing.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i>
                Billing & Plans
            </a>
        @endif

        @if(auth()->user()->hasAnyRole('admin', 'manager'))
            <a href="{{ route('admin.settings.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        @endif

        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf

            <button type="submit"
                    class="sidebar-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </button>
        </form>

    </div>

</aside>
