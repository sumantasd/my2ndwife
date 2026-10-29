<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isDeliveryBoy()) {
                return redirect()->route('delivery.dashboard');
            }
            if ($user->isAdmin() || $user->isStaff()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        return view('delivery.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginType = (string) $request->input('login_type', '');
        $phoneInput = trim((string) $request->input('phone', ''));
        $countryCode = trim((string) $request->input('country_code', '+91'));
        $emailInput = trim((string) $request->input('email', ''));
        $genericLogin = trim((string) $request->input('login', ''));
        $password = (string) $request->input('password', '');
        $remember = $request->boolean('remember');

        // Determine effective identifier and type
        $identifier = '';
        $isPhoneLogin = false;

        if ($loginType === 'phone' || (!empty($phoneInput) && empty($emailInput))) {
            $isPhoneLogin = true;
            $identifier = $phoneInput;
        } elseif ($loginType === 'email' || (!empty($emailInput) && empty($phoneInput))) {
            $isPhoneLogin = false;
            $identifier = $emailInput;
        } elseif (!empty($genericLogin)) {
            // Auto-detect based on presence of '@' or digits
            if (str_contains($genericLogin, '@')) {
                $isPhoneLogin = false;
                $identifier = $genericLogin;
            } else {
                $isPhoneLogin = true;
                $identifier = $genericLogin;
            }
        }

        $errorKey = $isPhoneLogin ? 'phone' : 'email';

        // Validation
        if (empty($identifier)) {
            $msg = $isPhoneLogin
                ? 'Please enter your registered 10-digit mobile number.'
                : 'Please enter your registered rider email address.';
            return back()->withErrors([$errorKey => $msg, 'login' => $msg])->withInput();
        }

        if (empty($password)) {
            return back()->withErrors(['password' => 'Please enter your password.'])->withInput();
        }

        // Rate limiting: 5 attempts per minute per identifier/IP
        $throttleKey = Str::transliterate(Str::lower($identifier) . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = "Too many login attempts. Please try again in {$seconds} seconds.";
            return back()->withErrors([$errorKey => $msg, 'login' => $msg])->withInput();
        }

        $user = null;

        if ($isPhoneLogin) {
            // Validate Indian mobile number format if country code is +91 or standard 10-12 digits
            $digits = User::extractIndianDigits($identifier);
            if (!$digits || !preg_match('/^[6-9]\d{9}$/', $digits)) {
                return back()->withErrors([
                    'phone' => 'Please enter a valid 10-digit Indian mobile number (e.g. 9830099887).',
                    'login' => 'Please enter a valid 10-digit Indian mobile number.',
                ])->withInput();
            }

            // Look up delivery partner by phone with exact matching
            $user = User::findByPhone($digits);
        } else {
            // Validate email format
            if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return back()->withErrors([
                    'email' => 'Please enter a valid email address.',
                    'login' => 'Please enter a valid email address.',
                ])->withInput();
            }

            $user = User::where('email', Str::lower($identifier))->first();
        }

        if (!$user || !Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            $errorMsg = $isPhoneLogin
                ? 'Invalid mobile number or password for delivery partner.'
                : 'Invalid email or password for delivery partner.';
            return back()->withErrors([
                $errorKey => $errorMsg,
                'login' => $errorMsg,
            ])->withInput();
        }

        if (!$user->isDeliveryBoy() && !$user->isAdmin()) {
            return back()->withErrors([
                $errorKey => 'Access denied: This portal is exclusively for registered delivery partners.',
                'login' => 'Access denied: This portal is exclusively for registered delivery partners.',
            ])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors([
                $errorKey => 'Your delivery partner account is deactivated. Please contact restaurant dispatch.',
                'login' => 'Your delivery partner account is deactivated. Please contact restaurant dispatch.',
            ])->withInput();
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditLog::record(
            action: 'delivery_rider_login',
            targetType: 'User',
            targetId: $user->id,
            details: [
                'name' => $user->name,
                'phone' => $user->phone,
                'method' => $isPhoneLogin ? 'mobile' : 'email',
            ]
        );

        if ($user->isDeliveryBoy()) {
            return redirect()->intended(route('delivery.dashboard'))->with('success', "Welcome back, {$user->name}!");
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::record(
                action: 'delivery_rider_logout',
                targetType: 'User',
                targetId: Auth::id()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery.login')->with('info', 'You have been logged out of the delivery portal.');
    }
}
