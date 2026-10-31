
@extends('layouts.guest')

@section('title', 'Login')

@section('content')

<div class="auth-card">

    <h4 class="fw-bold mb-2">Welcome Back 👋</h4>

    <p class="text-muted mb-4">
        Login to manage your restaurant business.
    </p>

    <form method="POST" action="{{ route('login.store') }}">

        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>

            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="form-control form-control-lg @error('email') is-invalid @enderror"
                   placeholder="admin@example.com"
                   required autofocus>

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>

            <input type="password"
                   name="password"
                   class="form-control form-control-lg"
                   placeholder="Enter password"
                   required>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div class="form-check">
                <input type="checkbox"
                       name="remember"
                       value="1"
                       class="form-check-input"
                       id="remember">

                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>

            <a href="{{ route('password.request') }}"
               class="text-decoration-none small text-warning">
                Forgot password?
            </a>

        </div>

        <button type="submit"
                class="btn auth-btn w-100 btn-lg">
            Login to Dashboard
        </button>

    </form>

    <div class="text-center mt-4">
        <span class="text-muted">Don't have an account?</span>

        <a href="{{ route('register') }}"
           class="text-warning fw-semibold text-decoration-none">
            Register
        </a>
    </div>

</div>

@endsection
