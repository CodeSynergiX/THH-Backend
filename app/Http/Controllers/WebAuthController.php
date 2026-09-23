<?php

namespace App\Http\Controllers;

use App\Domains\Users\RoleHome;
use App\Domains\Users\Services\OtpService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class WebAuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    /**
     * Show the dignified NGO web login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('auth/login');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('auth/forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink(['email' => $validated['email']]);

        return back()->with('status', 'If that email exists, a reset link was sent.');
    }

    public function showResetPassword(Request $request, string $token): Response
    {
        return Inertia::render('auth/reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($validated, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Password updated. Sign in with your new password.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function requestLoginOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $this->otp->issue(null, $validated['email'], 'login');

        return back()->with('status', 'OTP sent to your email if the account exists.');
    }

    public function verifyLoginOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        if (! $this->otp->verify(null, $validated['email'], $validated['code'], 'login')) {
            return back()->withErrors(['code' => 'Invalid or expired OTP.']);
        }

        $user = User::query()->where('email', $validated['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'No account found for this email.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        return redirect()->intended(RoleHome::url($user));
    }

    /**
     * Authenticate via email and password.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended(RoleHome::url($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Quick-sign-in helper for development and demo testing.
     * Ensures realistic role accounts exist and logs in immediately.
     */
    public function quickLogin(Request $request, string $role): RedirectResponse
    {
        $allowedRoles = ['super_admin', 'admin', 'staff', 'mentor', 'partner', 'citizen'];
        if (! in_array($role, $allowedRoles)) {
            abort(400, 'Invalid role');
        }

        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);

        $roleData = match ($role) {
            'super_admin' => [
                'name' => 'Rajeshbhai Patel (GGVT Director)',
                'email' => 'superadmin@ggvt.org',
                'phone' => '9825000001',
            ],
            'admin' => [
                'name' => 'Anilbhai Vasava (Admin)',
                'email' => 'admin@ggvt.org',
                'phone' => '9825000002',
            ],
            'staff' => [
                'name' => 'Sureshbhai Gamit (Dediapada Staff)',
                'email' => 'staff@ggvt.org',
                'phone' => '9825000003',
            ],
            'mentor' => [
                'name' => 'Dr. Meenaben Chaudhari (Education Mentor)',
                'email' => 'mentor@ggvt.org',
                'phone' => '9825000004',
            ],
            'partner' => [
                'name' => 'Vikrambhai Rathwa (Tribal Solar Partner)',
                'email' => 'partner@ggvt.org',
                'phone' => '9825000005',
            ],
            'citizen' => [
                'name' => 'Rameshbhai Bhil (Citizen)',
                'email' => 'citizen@ggvt.org',
                'phone' => '9825000006',
            ],
        };

        $user = User::firstOrCreate(
            ['email' => $roleData['email']],
            [
                'name' => $roleData['name'],
                'phone' => $roleData['phone'],
                'password' => Hash::make('Secret123!'),
                'locale' => 'gu',
                'is_active' => true,
            ]
        );

        // Assign role if not already assigned
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(RoleHome::url($user));
    }

    /**
     * Log out the active web user.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
