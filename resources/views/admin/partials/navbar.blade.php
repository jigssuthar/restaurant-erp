
<header class="admin-topbar">

    <div class="d-flex align-items-center gap-3">

        <button class="btn btn-light d-lg-none"
                type="button"
                onclick="document.getElementById('adminSidebar').classList.toggle('show')">
            <i class="bi bi-list fs-4"></i>
        </button>

        <h5 class="mb-0 fw-bold">Dashboard</h5>

    </div>

    <div class="d-flex align-items-center gap-3">

        <span class="text-muted small d-none d-sm-inline">
            {{ auth()->user()->name ?? 'Admin' }}
        </span>

        <div class="rounded-circle bg-warning-subtle text-warning
                    d-flex align-items-center justify-content-center"
             style="width:42px;height:42px">
            <i class="bi bi-person-fill fs-5"></i>
        </div>

    </div>

</header>
