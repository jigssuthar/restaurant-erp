@extends('layouts.website')

@section('title', 'RestaurantOS | Restaurant and Cafe Management')
@section('description', 'Manage restaurant orders, tables, menus, staff, and branches from one responsive restaurant management platform.')

@section('content')
<section class="marketing-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow"><span class="eyebrow-dot"></span>THE RESTAURANT OPERATIONS PLATFORM</span>
                <h1 class="marketing-title mt-4">Run service with <span>more clarity.</span></h1>
                <p class="marketing-lead mt-4">Bring orders, tables, menus, staff, and branches together in one workspace built for the pace of hospitality.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('register') }}" class="btn btn-orange btn-lg">Get started <i class="bi bi-arrow-up-right ms-2"></i></a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-orange btn-lg"><i class="bi bi-play-circle me-2"></i>Talk to our team</a>
                </div>
                <div class="hero-proof mt-4">
                    <span><i class="bi bi-check-circle-fill"></i>Simple to organize</span>
                    <span><i class="bi bi-check-circle-fill"></i>Built for growing teams</span>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-art-wrap">
                    <img src="{{ asset('images/restaurant-dashboard.svg') }}" alt="RestaurantOS dashboard illustration showing restaurant orders and sales" class="hero-art">
                    <div class="hero-float-card">
                        <span class="hero-float-icon"><i class="bi bi-qr-code-scan"></i></span>
                        <span><strong>Table QR ordering</strong><small>Orders sent to the right table</small></span>
                        <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                    </div>
                    <div class="hero-art-caption"><span class="status-pulse"></span>One view for your daily service</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="business-strip">
    <div class="container">
        <p class="text-center">ONE PLATFORM, MANY WAYS TO SERVE</p>
        <div class="row g-3 justify-content-center">
            @foreach($businessTypes as $business)
                <div class="col-6 col-md-3">
                    <div class="business-type"><i class="bi {{ $business['icon'] }}"></i><span>{{ $business['name'] }}</span></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space" id="features">
    <div class="container">
        <div class="section-intro text-center">
            <span class="eyebrow">EVERYTHING IN ONE PLACE</span>
            <h2 class="section-heading mt-3">Less juggling. <span>Better service.</span></h2>
            <p class="section-description">A connected set of tools for the work that happens before, during, and after every order.</p>
        </div>
        <div class="row g-4 mt-2">
            @foreach($features as $feature)
                <div class="col-md-6 col-lg-4">
                    <article class="feature-box feature-box-modern">
                        <div class="feature-icon"><i class="bi {{ $feature['icon'] }}"></i></div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['description'] }}</p>
                        <a href="{{ route('features') }}" aria-label="Learn more about {{ $feature['title'] }}">Explore feature <i class="bi bi-arrow-right"></i></a>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space platform-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="platform-image-frame">
                    <img src="{{ asset('images/restaurant-service.svg') }}" alt="Illustration of restaurant staff managing table service and orders" loading="lazy">
                    <div class="image-note"><i class="bi bi-lightning-charge-fill"></i> Made for the pace of service</div>
                </div>
            </div>
            <div class="col-lg-6">
                <span class="eyebrow">A BETTER VIEW OF THE DAY</span>
                <h2 class="section-heading mt-3">The details your team needs, right where they need them.</h2>
                <p class="section-description mt-3">Keep your branch information, menus, tables, and orders connected, with access designed around your team.</p>
                <div class="benefit-list mt-4">
                    <div><i class="bi bi-check2"></i><span><strong>See order progress</strong><small>Follow orders through clear service statuses.</small></span></div>
                    <div><i class="bi bi-check2"></i><span><strong>Keep tables organized</strong><small>Manage seating and table-linked guest orders.</small></span></div>
                    <div><i class="bi bi-check2"></i><span><strong>Grow branch by branch</strong><small>Keep teams and operations in the right scope.</small></span></div>
                </div>
                <a href="{{ route('about') }}" class="btn btn-orange mt-4">Discover RestaurantOS <i class="bi bi-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="section-space" id="solutions">
    <div class="container">
        <div class="section-intro text-center">
            <span class="eyebrow">MADE FOR HOSPITALITY</span>
            <h2 class="section-heading mt-3">Fits the way <span>you serve.</span></h2>
            <p class="section-description">Choose a workflow that makes sense for your food business.</p>
        </div>
        <div class="row g-4 mt-2">
            @foreach($solutions as $solution)
                <div class="col-md-4">
                    <article class="solution-card">
                        <div class="solution-icon"><i class="bi {{ $solution['icon'] }}"></i></div>
                        <h3>{{ $solution['title'] }}</h3>
                        <p>{{ $solution['description'] }}</p>
                        <a href="{{ route($solution['route']) }}">Explore solution <i class="bi bi-arrow-up-right"></i></a>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space steps-section">
    <div class="container">
        <div class="section-intro text-center">
            <span class="eyebrow">GET STARTED</span>
            <h2 class="section-heading mt-3">A simple way to get <span>up and running.</span></h2>
        </div>
        <div class="row g-4 mt-2">
            @foreach($steps as $step)
                <div class="col-md-4">
                    <article class="step-card">
                        <span>{{ $step['number'] }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="cta-panel">
            <div>
                <span class="eyebrow">YOUR NEXT SERVICE STARTS HERE</span>
                <h2>Bring your restaurant operations into focus.</h2>
                <p>Set up your workspace and see how a more connected day can feel.</p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn btn-orange btn-lg">Create your account <i class="bi bi-arrow-right ms-2"></i></a>
                <a href="{{ route('pricing') }}" class="btn btn-outline-light btn-lg">Explore plans</a>
            </div>
        </div>
    </div>
</section>
@endsection
