@extends('layouts.website')

@section('title', $page['title'].' | RestaurantOS')
@section('description', $page['description'])

@section('content')
<section class="inner-page-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">{{ $page['label'] }}</span>
                <h1 class="fw-bold mt-3 mb-3">{{ $page['heading'] }}</h1>
                <p class="section-description fs-5 mb-4">{{ $page['description'] }}</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-orange">Get started <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-orange">Talk to our team</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="inner-page-visual">
                    <img src="{{ asset('images/restaurant-dashboard.svg') }}" alt="RestaurantOS dashboard illustration" fetchpriority="high">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow">A CONNECTED WORKSPACE</span>
                <h2 class="section-heading mt-3">{{ $page['intro_title'] }}</h2>
                <p class="section-description mt-3">{{ $page['intro'] }}</p>
            </div>
            <div class="col-lg-6">
                <div class="highlight-panel">
                    <h2 class="h5 fw-bold mb-3">Designed for the way teams work</h2>
                    @foreach($page['highlights'] as $highlight)
                        <div class="highlight-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>{{ $highlight }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-space bg-light">
    <div class="container">
        <div class="section-intro text-center">
            <span class="eyebrow">EXPLORE THE PLATFORM</span>
            <h2 class="section-heading mt-3">Tools to keep service <span>in motion.</span></h2>
        </div>
        <div class="row g-4">
            @foreach($page['features'] as $feature)
                <div class="col-md-6 col-lg-4">
                    <article class="feature-box feature-box-modern">
                        <div class="feature-icon"><i class="bi {{ $feature['icon'] }}"></i></div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['description'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

@unless(in_array(request()->route()->parameter('slug'), ['privacy', 'terms'], true))
    <section class="section-space">
        <div class="container">
            <div class="row g-4">
                @foreach($page['benefits'] as $benefit)
                    <div class="col-md-4">
                        <div class="step-card">
                            <span class="solution-icon"><i class="bi bi-check2-circle"></i></span>
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endunless

<section class="section-space">
    <div class="container">
        <div class="cta-panel">
            <div>
                <span class="eyebrow">GET STARTED WITH RESTAURANTOS</span>
                <h2>Make your next shift feel more organized.</h2>
                <p>Set up your restaurant workspace and bring the everyday details together.</p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn btn-orange">Create your account <i class="bi bi-arrow-right ms-2"></i></a>
                <a href="{{ route('contact') }}" class="btn btn-outline-light">Contact our team</a>
            </div>
        </div>
    </div>
</section>
@endsection
