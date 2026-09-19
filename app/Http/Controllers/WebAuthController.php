<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class WebAuthController extends Controller
{
    /**
     * Show the dignified NGO web login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('auth/login');
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

            return redirect()->intended('/admin/dashboard');
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

        if ($role === 'citizen') {
            return redirect('/');
        }

        return redirect('/admin/dashboard');
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
