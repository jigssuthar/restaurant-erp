
<header class="admin-header">

    <div class="d-flex align-items-center gap-3">

        <button class="sidebar-toggle d-lg-none"
                id="sidebarToggle"
                type="button">
            <i class="bi bi-list"></i>
        </button>

        <div>
            <h6 class="fw-bold mb-0">Admin Panel</h6>
            <small class="text-muted d-none d-sm-block">
                Restaurant Management System
            </small>
        </div>

    </div>

    <div class="d-flex align-items-center gap-3">

        <button class="btn btn-light rounded-circle"
                type="button"
                aria-label="Notifications">
            <i class="bi bi-bell"></i>
        </button>

        <div class="dropdown">

            <button class="btn d-flex align-items-center gap-2"
                    data-bs-toggle="dropdown">

                <div class="rounded-circle bg-warning-subtle text-warning
                            d-flex align-items-center justify-content-center"
                     style="width:38px;height:38px">
                    <i class="bi bi-person"></i>
                </div>

                <span class="d-none d-sm-block fw-semibold">
                    {{ auth()->user()->name ?? 'Admin' }}
                </span>

                <i class="bi bi-chevron-down small"></i>

            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>

            </ul>

        </div>

    </div>

</header>
