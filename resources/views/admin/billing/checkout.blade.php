@extends('layouts.admin')

@section('title', 'Secure checkout')

@section('content')
<div class="admin-card mx-auto" style="max-width: 640px">
    <h1 class="h4">Subscribe to {{ $plan->name }}</h1>
    <p class="text-muted">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} / {{ $plan->billing_interval === 'yearly' ? 'year' : 'month' }}</p>
    <p>Razorpay will securely collect payment details. Your subscription is activated only after the payment signature is verified.</p>
    <button id="razorpay-checkout" class="btn btn-orange">Pay securely with Razorpay</button>
    <a href="{{ route('admin.billing.index') }}" class="btn btn-light border ms-2">Cancel</a>
    <div id="checkout-error" class="alert alert-danger mt-3 d-none" role="alert"></div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('razorpay-checkout').addEventListener('click', function () {
    const checkout = new Razorpay({
        key: @json($razorpayKey),
        subscription_id: @json($subscription->provider_subscription_id),
        name: @json($checkoutName),
        description: @json($plan->name),
        prefill: {
            name: @json(auth()->user()->name),
            email: @json(auth()->user()->email)
        },
        handler: async function (response) {
            const result = await fetch(@json(route('admin.billing.verify')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify(response)
            });
            if (result.ok) {
                window.location.href = @json(route('admin.billing.index'));
                return;
            }
            const message = document.getElementById('checkout-error');
            const details = await result.json().catch(() => ({}));
            message.textContent = details.message || 'Payment verification failed. Do not retry immediately; contact support with your payment reference.';
            message.classList.remove('d-none');
        },
        modal: { ondismiss: function () { window.location.href = @json(route('admin.billing.index')); } },
        theme: { color: '#f97316' }
    });
    checkout.on('payment.failed', function () {
        const message = document.getElementById('checkout-error');
        message.textContent = 'The payment was not completed. Please try again or contact support.';
        message.classList.remove('d-none');
    });
    checkout.open();
});
</script>
@endsection
