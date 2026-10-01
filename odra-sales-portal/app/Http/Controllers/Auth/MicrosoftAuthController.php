<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftAuthController extends Controller
{
    /**
     * Redirect to Microsoft Entra ID authentication page.
     */
    public function redirectToMicrosoft(): RedirectResponse
    {
        $clientId = config('services.microsoft.client_id');

        if (! $clientId) {
            return redirect()->route('login')->withErrors([
                'login' => 'Microsoft 365 Single Sign-On is not configured on this environment yet. Please log in using your Office Email / Company ID and Password below.',
            ]);
        }

        try {
            return Socialite::driver('microsoft')->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'login' => 'Unable to connect to Microsoft 365 SSO: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Microsoft Entra ID callback.
     */
    public function handleMicrosoftCallback(): RedirectResponse
    {
        try {
            $msUser = Socialite::driver('microsoft')->user();
        } catch (Exception $e) {
            AuditService::log(
                action: 'MICROSOFT_AUTH_FAILED',
                notes: 'OAuth callback error: '.$e->getMessage()
            );

            return redirect()->route('login')->withErrors([
                'login' => 'Microsoft authentication failed. Please try again or use your password.',
            ]);
        }

        // Match user by entra_object_id or email
        $user = User::where('entra_object_id', $msUser->getId())
            ->orWhere('email', $msUser->getEmail())
            ->first();

        if (! $user) {
            AuditService::log(
                action: 'MICROSOFT_AUTH_UNREGISTERED',
                notes: "Unregistered Microsoft account attempted login: {$msUser->getEmail()} (ID: {$msUser->getId()})"
            );

            return redirect()->route('login')->withErrors([
                'login' => 'Your Microsoft account ('.$msUser->getEmail().') is not registered in the ODRA Sales Portal. Please contact your administrator to provision access.',
            ]);
        }

        if (! $user->is_active) {
            AuditService::log(
                action: 'MICROSOFT_AUTH_INACTIVE',
                userId: $user->id,
                notes: "Deactivated user attempted Microsoft login: {$user->email}"
            );

            return redirect()->route('login')->withErrors([
                'login' => 'Your account has been deactivated. Please contact your system administrator.',
            ]);
        }

        // Link Microsoft Entra ID object id if not linked yet
        if (! $user->entra_object_id) {
            $user->update(['entra_object_id' => $msUser->getId()]);
        }

        $user->update([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);
        request()->session()->regenerate();

        AuditService::log(
            action: 'LOGIN_SUCCESS_SSO',
            userId: $user->id,
            notes: "Successful Microsoft Entra ID SSO login ({$user->role?->name})"
        );

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('sales.dashboard'));
    }
}
