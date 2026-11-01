
@extends('layouts.website')

@section('title', 'Contact Us | DineCentral')

@section('content')

<section class="inner-page-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="eyebrow">CONTACT RESTAURANTOS</span>
                <h1 class="section-heading display-4 mt-3">Let’s talk about your business.</h1>
                <p class="section-description fs-5 mt-3 mb-0">Tell us what you’re building. Our team will follow up about your restaurant management needs.</p>
            </div>
            <div class="col-lg-5">
                <figure class="inner-page-visual inner-page-photo mb-0">
                    <img src="{{ asset('images/restaurant-team.jpg') }}" alt="Two cooks preparing food together in a kitchen" fetchpriority="high">
                    <figcaption>Tell us about your team, locations, and service workflow.</figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">

            {{-- CONTACT DETAILS --}}
            <div class="col-lg-5">

                <h2 class="h3 fw-bold mb-3">Get in touch</h2>

                <p class="text-muted mb-4">
                    Send a message using the form and include the best way to reach you.
                </p>

                <div class="contact-info-card mb-3">
                    <div class="contact-icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <div>
                        <h3>Product questions</h3>
                        <p>Ask us about restaurant workflows, features, or getting started.</p>
                    </div>
                </div>

                <div class="contact-info-card mb-3">
                    <div class="contact-icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <div>
                        <h3>Pricing and plans</h3>
                        <p>Tell us about your business and the locations you manage.</p>
                    </div>
                </div>

                <div class="contact-info-card mb-3">
                    <div class="contact-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <div>
                        <h3>Personalized walkthrough</h3>
                        <p>Request a demo to explore the platform for your team.</p>
                    </div>
                </div>

                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div>
                        <h3>Follow-up</h3>
                        <p>Leave your contact details and our team can get back to you.</p>
                    </div>
                </div>

            </div>

            {{-- CONTACT FORM --}}
            <div class="col-lg-7">

                <div class="contact-form-card">

                    <h3 class="fw-bold mb-2">Send Us A Message</h3>

                    <p class="text-muted mb-4">
                        Fill out the form and tell us about your requirements.
                    </p>

                    <form action="{{ route('contact.store') }}"
                          method="POST">

                        @csrf

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>

                                <input type="text"
                                       name="name"
                                       value="{{ old('name') }}"
                                       class="form-control"
                                       placeholder="Enter your name"
                                       maxlength="100"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>

                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control"
                                       placeholder="Enter your email"
                                       maxlength="150"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>

                                <input type="tel"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       class="form-control"
                                       placeholder="Enter phone number"
                                       maxlength="20">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Business Type *</label>

                                <select name="business_type"
                                class="form-select @error('business_type') is-invalid @enderror" required>

                                    <option value="">Select Business</option>

                                    @foreach(['Restaurant', 'Cafe', 'Cloud Kitchen', 'Restaurant Chain', 'Other'] as $businessType)
                                <option value="{{ $businessType }}" @selected(old('business_type') === $businessType)>{{ $businessType }}</option>
                                    @endforeach

                                </select>
                                @error('business_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Subject *</label>

                                <input type="text"
                                       name="subject"
                                       value="{{ old('subject') }}"
                                       class="form-control"
                                       placeholder="Enter subject"
                                       maxlength="150"
                                       required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message *</label>

                                <textarea name="message"
                                          class="form-control"
                                          rows="5"
                                          maxlength="3000"
                                          placeholder="Write your message..."
                                          required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12">

                                <button type="submit"
                                        class="btn btn-orange w-100">

                                    Send Message
                                    <i class="bi bi-send ms-2"></i>

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
.contact-info-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    background: #fff;
    border: 1px solid #edf0f4;
    border-radius: 12px;
}

.contact-icon {
    min-width: 48px;
    height: 48px;
    background: #fff1e7;
    color: #f97316;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 22px;
}

.contact-info-card h6 {
    font-weight: 700;
    margin-bottom: 5px;
}

.contact-info-card p {
    color: #64748b;
    margin: 0;
    overflow-wrap: anywhere;
}

.contact-form-card {
    background: white;
    border: 1px solid #edf0f4;
    border-radius: 16px;
    padding: 35px;
    box-shadow: 0 10px 35px rgba(0,0,0,.04);
}

.contact-form-card textarea {
    resize: vertical;
}

@media(max-width:767px) {
    .contact-form-card {
        padding: 20px;
    }

    .contact-info-card {
        padding: 15px;
    }
}
</style>
@endpush
