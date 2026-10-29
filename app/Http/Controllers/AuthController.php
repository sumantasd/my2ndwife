<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginInput = trim((string) ($request->input('login') ?? $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $remember = $request->boolean('remember');

        $request->validate([
            'login' => ['sometimes', 'required', 'string'],
            'email' => ['sometimes', 'required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (empty($loginInput)) {
            return back()->withErrors([
                'login' => 'Please enter your registered email address or mobile number.',
                'email' => 'Please enter your registered email address or mobile number.',
            ])->withInput();
        }

        $isEmail = str_contains($loginInput, '@');

        // If not an email, validate Indian mobile number format
        if (!$isEmail) {
            $digits = User::extractIndianDigits($loginInput);
            if (!$digits || !preg_match('/^[6-9]\d{9}$/', $digits)) {
                return back()->withErrors([
                    'login' => 'Please enter a valid email address or 10-digit mobile number.',
                    'email' => 'Please enter a valid email address or 10-digit mobile number.',
                ])->withInput();
            }
        }

        $user = User::findByEmailOrPhone($loginInput);

        if ($user && Hash::check($password, $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors([
                    'login' => 'Your account has been deactivated. Please contact restaurant administration.',
                    'email' => 'Your account has been deactivated. Please contact restaurant administration.',
                ])->withInput();
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            AuditLog::record(
                action: 'user_login',
                targetType: 'User',
                targetId: $user->id,
                details: ['role' => $user->role, 'login_method' => $isEmail ? 'email' : 'phone']
            );

            return $this->redirectUser($user);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $phoneDigits = User::extractIndianDigits($validated['phone']);
        if (!$phoneDigits || !preg_match('/^[6-9]\d{9}$/', $phoneDigits)) {
            return back()->withErrors([
                'phone' => 'Please enter a valid 10-digit Indian mobile number (e.g. 98300 12345).',
            ])->withInput();
        }

        // Prevent duplicate accounts with same normalized mobile number
        if (User::findByPhone($phoneDigits)) {
            return back()->withErrors([
                'phone' => 'This mobile number is already registered. Please sign in or use a different number.',
            ])->withInput();
        }

        $normalizedPhone = '+91 ' . $phoneDigits;

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => $normalizedPhone,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::record(
            action: 'user_registered',
            targetType: 'User',
            targetId: $user->id
        );

        return redirect()->intended(route('home'))->with('success', "Welcome to My 2nd Wife, {$user->name}! Your account is ready.");
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::record(
                action: 'user_logout',
                targetType: 'User',
                targetId: Auth::id()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out securely.');
    }

    private function redirectUser(User $user): RedirectResponse
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user->isDeliveryBoy()) {
            return redirect()->intended(route('delivery.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
