
@extends('layouts.admin')

@section('title', 'Organization Details')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

    <div>
        <h3 class="fw-bold mb-1">Organization Details</h3>
        <p class="text-muted mb-0">Complete business profile.</p>
    </div>

    <a href="{{ route('admin.organizations.edit', $organization) }}"
       class="btn btn-orange">
        <i class="bi bi-pencil me-2"></i>Edit Organization
    </a>

</div>

<div class="row g-4">

    <div class="col-lg-4">

        <div class="admin-card text-center">

            @if($organization->logo)
                <img src="{{ asset('storage/'.$organization->logo) }}"
                     width="110" height="110"
                     class="rounded-circle mb-3"
                     style="object-fit:cover"
                     alt="Organization logo">
            @else
                <div class="stat-icon orange-bg mx-auto mb-3">
                    <i class="bi bi-building"></i>
                </div>
            @endif

            <h4 class="fw-bold">{{ $organization->name }}</h4>

            <p class="text-muted">{{ $organization->slug }}</p>

            <span class="status-badge {{ $organization->status == 'active' ? 'status-active' : 'status-inactive' }}">
                {{ ucfirst($organization->status) }}
            </span>

            <hr>

            <div class="text-start">
                <p class="text-muted small mb-1">Organization ID</p>
                <h6>#{{ $organization->id }}</h6>
            </div>

        </div>

    </div>

    <div class="col-lg-8">

        <div class="admin-card">

            <h5 class="fw-bold mb-4">Business Information</h5>

            <div class="row g-4">

                <div class="col-md-6">
                    <small class="text-muted">Email Address</small>
                    <p class="fw-semibold">{{ $organization->email ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Phone Number</small>
                    <p class="fw-semibold">{{ $organization->phone ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">GST Number</small>
                    <p class="fw-semibold">{{ $organization->gst_number ?? '-' }}</p>
                </div>

                <div class="col-md-6">
                    <small class="text-muted">Created Date</small>
                    <p class="fw-semibold">{{ $organization->created_at->format('d M Y') }}</p>
                </div>

                <div class="col-12">
                    <small class="text-muted">Business Address</small>
                    <p class="fw-semibold">{{ $organization->address ?? '-' }}</p>
                </div>

                <div class="admin-card">
                    <h5 class="fw-bold mb-3">Subscription</h5>
                    @if($subscription = $organization->currentSubscription)
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted">Status</small>
                                <p class="fw-semibold mb-0">{{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Plan</small>
                                <p class="fw-semibold mb-0">{{ $subscription->plan?->name ?? 'Free trial' }}</p>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Trial ends</small>
                                <p class="fw-semibold mb-0">{{ $subscription->trial_ends_at?->format('d M Y H:i') ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Current period ends</small>
                                <p class="fw-semibold mb-0">{{ $subscription->current_period_end?->format('d M Y') ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Razorpay subscription</small>
                                <p class="fw-semibold mb-0">{{ $subscription->provider_subscription_id ?? '—' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-warning mb-0">No subscription record found.</p>
                    @endif
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
