@extends('layouts.website')

@section('title', $page['title'].' | DineCentral')
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
                    @if($page['is_legal'] ?? false)
                        <a href="{{ route('contact') }}" class="btn btn-orange">Ask a question <i class="bi bi-arrow-right ms-2"></i></a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-orange">Get started <i class="bi bi-arrow-right ms-2"></i></a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-orange">Talk to our team</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-5">
                <figure class="inner-page-visual {{ str_ends_with($page['image'], '.jpg') ? 'inner-page-photo' : '' }} mb-0">
                    <img src="{{ asset('images/'.$page['image']) }}" alt="{{ $page['image_alt'] }}" fetchpriority="high">
                    <figcaption>{{ $page['image_caption'] }}</figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="eyebrow">{{ $page['label'] }}</span>
                <h2 class="section-heading mt-3">{{ $page['section_title'] }}</h2>
                <p class="section-description mt-3">{{ $page['section_text'] }}</p>
            </div>
            <div class="col-lg-6">
                <div class="highlight-panel">
                    <h2 class="h5 fw-bold mb-3">{{ $page['highlight_title'] }}</h2>
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
            <span class="eyebrow">{{ $page['label'] }}</span>
            <h2 class="section-heading mt-3">{{ $page['feature_heading'] }}</h2>
        </div>
        <div class="row g-4">
            @foreach($page['features'] as $feature)
                <div class="col-md-6 col-lg-4 @if(isset($feature['id'])) feature-module-anchor @endif" @if(isset($feature['id'])) id="module-{{ $feature['id'] }}" @endif>
                    <article class="feature-box feature-box-modern {{ isset($feature['image']) ? 'feature-module-card' : '' }}">
                        @if(isset($feature['image']))
                            <img class="feature-module-image" src="{{ asset('images/'.$feature['image']) }}" alt="{{ $feature['image_alt'] }}" loading="lazy">
                        @endif
                        <div class="feature-icon"><i class="bi {{ $feature['icon'] }}"></i></div>
                        <h3>{{ $feature['title'] }}</h3>
                        <p>{{ $feature['description'] }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section-space workflow-section">
    <div class="container">
        <div class="section-intro text-center">
            <span class="eyebrow">HOW IT WORKS</span>
            <h2 class="section-heading mt-3">{{ $page['workflow_title'] }}</h2>
        </div>
        <div class="row g-4">
            @foreach($page['workflow'] as $index => $step)
                <div class="col-md-4">
                    <article class="workflow-card h-100">
                        <span class="workflow-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
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
                <span class="eyebrow">{{ $page['is_legal'] ?? false ? 'NEED MORE INFORMATION?' : 'GET STARTED WITH RESTAURANTOS' }}</span>
                <h2>{{ $page['cta_title'] }}</h2>
                <p>{{ $page['cta_text'] }}</p>
            </div>
            <div class="d-flex flex-wrap gap-3">
                @if($page['is_legal'] ?? false)
                    <a href="{{ route('contact') }}" class="btn btn-orange">Contact our team <i class="bi bi-arrow-right ms-2"></i></a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-orange">Create your account <i class="bi bi-arrow-right ms-2"></i></a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-light">Contact our team</a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
