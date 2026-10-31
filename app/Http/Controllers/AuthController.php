<?php

namespace App\Http\Controllers;
use App\Models\Branch;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back!');
        }

        return back()
            ->withErrors([
                'email' => 'Invalid email or password.',
            ])
            ->onlyInput('email');
    }

    // Show registration page
    public function showRegister()
    {
        return view('auth.register');
    }

    // Process registration
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'organization_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:12'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $organization = Organization::create([
                'name' => $validated['organization_name'],
                'slug' => Str::slug($validated['organization_name']).'-'.Str::lower(Str::random(8)),
                'email' => $validated['email'],
            ]);

            $branch = Branch::create([
                'organization_id' => $organization->id,
                'name' => $validated['branch_name'],
                'branch_code' => 'MAIN',
                'status' => 'active',
            ]);

            OrganizationSetting::create([
                'organization_id' => $organization->id,
                'currency_code' => config('saas.currency', 'INR'),
                'tax_rate' => 0,
                'service_charge_rate' => 0,
                'delivery_fee' => 0,
            ]);

            return User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'role' => 'admin',
            ]);
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Restaurant workspace created. Your '.config('saas.trial_days', 30).'-day trial has started.');
    }

    // Show forgot password page
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Send password reset link
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ]);
    }

    // Show reset password page
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // Update password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:12'],
        ]);

        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Password reset successfully. Please login.');
        }

        return back()->withErrors([
            'email' => [__($status)],
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have logged out successfully.');
    }
}
