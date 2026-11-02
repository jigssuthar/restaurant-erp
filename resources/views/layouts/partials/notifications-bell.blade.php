<div
    class="dropdown"
    data-in-app-notifications
    data-feed-url="{{ route('notifications.index') }}"
    data-read-all-url="{{ route('notifications.read-all') }}"
    data-csrf-token="{{ csrf_token() }}"
>
    <button
        class="btn btn-light rounded-circle position-relative"
        type="button"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-label="Notifications"
        aria-expanded="false"
    >
        <i class="bi bi-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" data-notification-count></span>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0 shadow" style="width:min(360px, calc(100vw - 2rem))">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <strong>Notifications</strong>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-notifications-read-all hidden>
                Mark all as read
            </button>
        </div>
        <div class="list-group list-group-flush" data-notifications-list aria-live="polite">
            <div class="px-3 py-4 text-center text-muted small" data-notifications-status>Loading notifications…</div>
        </div>
    </div>
</div>
