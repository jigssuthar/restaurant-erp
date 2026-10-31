
@extends('layouts.guest')

@section('title', 'Register')

@section('content')

<div class="auth-card">

    <h4 class="fw-bold mb-2">Create Your Account</h4>

    <p class="text-muted mb-4">Create your restaurant workspace and start a {{ config('saas.trial_days', 30) }}-day free trial.</p>

    <form method="POST" action="{{ route('register.store') }}">

        @csrf

        <div class="mb-3">
            <label class="form-label">Full Name</label>

            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="form-control"
                   placeholder="Your name"
                   required>

            @error('name')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Restaurant Name</label>
            <input type="text" name="organization_name" value="{{ old('organization_name') }}"
                   class="form-control" placeholder="Your restaurant" required>
            @error('organization_name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">First Branch Name</label>
            <input type="text" name="branch_name" value="{{ old('branch_name', 'Main Branch') }}"
                   class="form-control" placeholder="Main Branch" required>
            @error('branch_name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>

            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="form-control"
                   placeholder="you@example.com"
                   required>

            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>

            <input type="password"
                   name="password"
                   class="form-control"
                   placeholder="Minimum 12 characters"
                   required>

            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password</label>

            <input type="password"
                   name="password_confirmation"
                   class="form-control"
                   placeholder="Confirm password"
                   required>
        </div>

        <button type="submit" class="btn auth-btn w-100 btn-lg">
            Create Account
        </button>

    </form>

    <div class="text-center mt-4">
        Already have an account?

        <a href="{{ route('login') }}"
           class="text-warning text-decoration-none fw-semibold">
            Login
        </a>
    </div>

</div>

@endsection
