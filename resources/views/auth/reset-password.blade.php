
@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')

<div class="auth-card">

    <h4 class="fw-bold mb-2">Reset Your Password</h4>

    <p class="text-muted mb-4">
        Enter your new password below.
    </p>

    <form method="POST" action="{{ route('password.update') }}">

        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label">Email Address</label>

            <input type="email"
                   name="email"
                   value="{{ old('email', $email) }}"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>

            <input type="password"
                   name="password"
                   class="form-control"
                   required>

            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm New Password</label>

            <input type="password"
                   name="password_confirmation"
                   class="form-control"
                   required>
        </div>

        <button class="btn auth-btn w-100 btn-lg">
            Update Password
        </button>

    </form>

</div>

@endsection
