
@extends('layouts.website')

@section('title', 'Pricing Plans | RestaurantOS')

@section('content')

<section class="inner-page-hero text-center">
    <div class="container">

        <span class="text-orange fw-bold">OUR PRICING</span>

        <h1 class="section-heading display-4 mt-3">
            Simple Pricing For Your Business
        </h1>

        <p class="section-description mt-3">
            Choose a plan based on your restaurant's operational requirements.
        </p>

    </div>
</section>

<section class="section-space pricing-context">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <img class="pricing-context-image" src="{{ asset('images/menu-food.jpg') }}" alt="Restaurant dishes ready for guests" loading="lazy">
            </div>
            <div class="col-lg-6">
                <span class="eyebrow">PLANS FOR YOUR OPERATION</span>
                <h2 class="section-heading mt-3">Choose a plan around the way you serve.</h2>
                <p class="section-description mt-3">Compare the published plan details and included limits for your restaurant. Branch, staff, product, and table limits depend on the plan configured by the platform operator.</p>
                <div class="pricing-context-points">
                    <div><i class="bi bi-check-circle-fill"></i><span>Review the billing interval and listed features before subscribing.</span></div>
                    <div><i class="bi bi-check-circle-fill"></i><span>Trial length is shown at signup and may vary by platform settings.</span></div>
                    <div><i class="bi bi-check-circle-fill"></i><span>RestaurantOS subscription billing is separate from customer food-order payments.</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">

    <div class="container">

        <div class="row g-4 justify-content-center">

            @forelse($plans as $plan)

                <div class="col-lg-4 col-md-6">

                    <article class="price-card-modern h-100">
                        <h4 class="fw-bold">{{ $plan->name }}</h4>

                        <div class="price-value my-3">
                            {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}
                            <small class="fs-6 text-muted fw-normal">/ {{ $plan->billing_interval === 'yearly' ? 'year' : 'month' }}</small>
                        </div>

                        <p class="text-muted">{{ $plan->description }}</p>

                        <hr>

                        @foreach($plan->features ?? [] as $feature)
                            <p>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                {{ $feature }}
                            </p>
                        @endforeach

                        <a href="{{ route('register') }}" class="btn btn-orange w-100 mt-3">
                            Start {{ config('saas.trial_days', 30) }}-day free trial
                        </a>

                    </article>

                </div>

            @empty
                <div class="col-lg-8">
                    <div class="alert alert-info text-center mb-0">
                        Subscription plans are being configured. Create an account to start your {{ config('saas.trial_days', 30) }}-day free trial.
                        <div><a href="{{ route('register') }}" class="btn btn-orange mt-3">Start free trial</a></div>
                    </div>
                </div>
            @endforelse

        </div>

        @if($plans->isNotEmpty())
            <p class="text-center text-muted small mt-4">A payment method is not required to begin your trial.</p>
        @endif

    </div>

</section>

@endsection
