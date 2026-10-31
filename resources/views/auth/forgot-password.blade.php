
@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')

<div class="auth-card">

    <h4 class="fw-bold mb-2">Forgot Password?</h4>

    <p class="text-muted mb-4">
        Enter your registered email to receive a password reset link.
    </p>

    <form method="POST" action="{{ route('password.email') }}">

        @csrf

        <div class="mb-4">
            <label class="form-label">Email Address</label>

            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="form-control form-control-lg"
                   placeholder="you@example.com"
                   required>

            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn auth-btn w-100 btn-lg">
            Send Reset Link
        </button>

    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}"
           class="text-warning text-decoration-none">
            Back to Login
        </a>
    </div>

</div>

@endsection
