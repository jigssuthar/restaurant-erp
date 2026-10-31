
<nav class="top-navbar">

    <div class="d-flex align-items-center gap-3">

        <button class="btn btn-light d-lg-none"
                onclick="toggleSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>

        <div>
            <h6 class="mb-0 fw-bold">
                @yield('page-heading', 'Dashboard')
            </h6>
            <small class="text-muted">
                Restaurant Management System
            </small>
        </div>

    </div>

    <div class="d-flex align-items-center gap-3">

        <select class="form-select form-select-sm"
                style="width:150px">
            <option>All Branches</option>
        </select>

        <button class="btn btn-light position-relative">
            <i class="bi bi-bell"></i>
        </button>

        <div class="dropdown">

            <button class="btn btn-light dropdown-toggle"
                    data-bs-toggle="dropdown">

                <i class="bi bi-person-circle me-1"></i>
                {{ auth()->user()?->name ?? 'Admin' }}

            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person me-2"></i>
                        Profile
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                 <form method="POST" action="{{ route('logout') }}">
    @csrf

    <button type="submit" class="dropdown-item text-danger">
        <i class="bi bi-box-arrow-right me-2"></i>
        Logout
    </button>
</form>
                </li>

            </ul>

        </div>

    </div>

</nav>
