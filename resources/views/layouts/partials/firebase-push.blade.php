<span
    data-firebase-push
    data-config='@json(config("services.firebase.web"))'
    data-worker-url="{{ route('firebase.messaging-sw') }}"
    data-register-url="{{ route('notifications.firebase.device.store') }}"
    data-csrf-token="{{ csrf_token() }}"
    class="small text-muted"
    role="status"
    aria-live="polite"
>
    <span data-firebase-push-status></span>
</span>
