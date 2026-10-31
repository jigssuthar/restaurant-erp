@extends('layouts.admin')

@section('title', 'Billing & Plans')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Billing & Plans</h1>
    <p class="text-muted mb-0">Manage your RestaurantOS trial, subscription, and payment history.</p>
</div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
@if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

<section class="admin-card">
    <h2 class="h5">Current subscription</h2>
    @if($subscription)
        <p class="mb-1">Status: <strong>{{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</strong></p>
        <p class="mb-1">Plan: <strong>{{ $subscription->plan?->name ?? 'Free trial' }}</strong></p>
        @if($subscription->status === 'trialing' && $subscription->trial_ends_at)
            <p class="text-muted mb-0">Trial ends {{ $subscription->trial_ends_at->format('M j, Y H:i') }}</p>
        @elseif($subscription->current_period_end)
            <p class="text-muted mb-0">Current billing period ends {{ $subscription->current_period_end->format('M j, Y') }}</p>
        @endif
        @if($subscription->status === 'active' && ! $subscription->cancel_at_period_end)
            <form method="POST" action="{{ route('admin.billing.cancel') }}" class="mt-3"
                  onsubmit="return confirm('Schedule cancellation at the end of the current billing period?')">
                @csrf
                <button class="btn btn-outline-danger btn-sm">Cancel renewal</button>
            </form>
        @elseif($subscription->cancel_at_period_end)
            <p class="text-warning mb-0">Renewal is cancelled; access remains until the current period ends.</p>
        @endif
    @else
        <p class="text-warning mb-0">No subscription record is associated with this workspace. Contact support.</p>
    @endif
</section>

<h2 class="h5 mb-3">Available plans</h2>
<div class="row g-3 mb-4">
    @forelse($plans as $plan)
        <div class="col-lg-4">
            <article class="admin-card h-100 mb-0">
                <h3 class="h5">{{ $plan->name }}</h3>
                <div class="fs-4 fw-bold">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}
                    <small class="fs-6 text-muted">/ {{ $plan->billing_interval === 'yearly' ? 'year' : 'month' }}</small>
                </div>
                @if($plan->description)<p class="text-muted mt-2">{{ $plan->description }}</p>@endif
                <ul class="small">
                    @foreach($plan->features ?? [] as $feature)<li>{{ $feature }}</li>@endforeach
                </ul>
                <form method="POST" action="{{ route('admin.billing.subscribe', $plan) }}">
                    @csrf
                    <button class="btn btn-orange w-100" @disabled($subscription?->status === 'active' && $subscription->grantsAccess())>
                        {{ $subscription?->status === 'active' ? 'Subscription active' : 'Continue to secure checkout' }}
                    </button>
                </form>
            </article>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info mb-0">Paid plans are not published yet. Your trial remains available until the date shown above.</div></div>
    @endforelse
</div>

<section class="admin-table-card table-responsive">
    <h2 class="h5">Payment history</h2>
    <table class="table align-middle">
        <thead><tr><th>Date</th><th>Payment reference</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($transactions as $transaction)
            <tr>
                <td>{{ $transaction->paid_at?->format('M j, Y H:i') ?? '—' }}</td>
                <td>{{ $transaction->provider_payment_id ?? $transaction->provider_invoice_id ?? '—' }}</td>
                <td>{{ $transaction->currency }} {{ number_format((float) $transaction->amount, 2) }}</td>
                <td>{{ ucfirst($transaction->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No payments recorded.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($transactions instanceof \Illuminate\Contracts\Pagination\Paginator){{ $transactions->links() }}@endif
</section>
@endsection
