<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the application login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->authenticatedRedirect(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt with Company ID or Email.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Please enter your Office Email or Company ID.',
            'password.required' => 'Please enter your password.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['login']).'|'.$request->ip());

        // Check rate limiter: 5 attempts per minute
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            AuditService::log(
                action: 'LOGIN_RATE_LIMITED',
                notes: "Rate limit triggered for login identifier: {$credentials['login']} (IP: {$request->ip()})"
            );

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => "Too many failed login attempts. Please try again in {$seconds} seconds."]);
        }

        // Find user by Email or Company ID
        $user = User::where('email', $credentials['login'])
            ->orWhere('company_id', $credentials['login'])
            ->first();

        if (! $user) {
            RateLimiter::hit($throttleKey, 60);

            AuditService::log(
                action: 'LOGIN_FAILED',
                notes: "User not found for identifier: {$credentials['login']}"
            );

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Invalid credentials. Please check your login details and try again.']);
        }

        // Check if account is locked
        if ($user->isLocked()) {
            AuditService::log(
                action: 'LOGIN_BLOCKED_LOCKED',
                userId: $user->id,
                notes: "Login attempted on locked account until {$user->locked_until}"
            );

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Your account is temporarily locked due to too many failed attempts. Please try again in 15 minutes or contact support.']);
        }

        // Check if account is active
        if (! $user->is_active) {
            AuditService::log(
                action: 'LOGIN_BLOCKED_INACTIVE',
                userId: $user->id,
                notes: 'Login attempted on deactivated account'
            );

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Your account has been deactivated. Please contact your system administrator.']);
        }

        // Verify password
        if (! $user->password || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            $user->increment('failed_login_count');

            if ($user->failed_login_count >= 5) {
                $user->update([
                    'locked_until' => now()->addMinutes(15),
                ]);

                AuditService::log(
                    action: 'ACCOUNT_LOCKED',
                    userId: $user->id,
                    notes: 'Account locked for 15 minutes after 5 consecutive failed login attempts'
                );

                return back()->withInput($request->only('login'))
                    ->withErrors(['login' => 'Your account has been locked for 15 minutes due to 5 consecutive failed login attempts.']);
            }

            AuditService::log(
                action: 'LOGIN_FAILED',
                userId: $user->id,
                notes: "Invalid password attempt (attempt {$user->failed_login_count}/5)"
            );

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'Invalid credentials. Please check your login details and try again.']);
        }

        // Success: reset rate limit & failure counters
        RateLimiter::clear($throttleKey);
        $user->update([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        AuditService::log(
            action: 'LOGIN_SUCCESS',
            userId: $user->id,
            notes: "Successful login via credentials ({$user->role?->name})"
        );

        return $this->authenticatedRedirect($user);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($userId) {
            AuditService::log(
                action: 'LOGOUT',
                userId: $userId,
                notes: 'User logged out'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been successfully logged out.');
    }

    /**
     * Redirect authenticated user based on role.
     */
    protected function authenticatedRedirect(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('sales.dashboard'));
    }
}
