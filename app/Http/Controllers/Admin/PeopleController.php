<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Users\Models\District;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PeopleController extends Controller
{
    /**
     * People & Roles Management directory.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $selectedRole = $request->query('role', 'staff');
        $search = $request->query('search', '');
        $districtId = $request->query('district_id', 'all');

        $query = ScopeHelper::applyUserScope(User::query(), $user);

        if ($selectedRole !== 'all') {
            $query->whereHas('roles', function ($q) use ($selectedRole) {
                $q->where('name', $selectedRole);
            });
        }

        if ($districtId !== 'all') {
            $query->where('district_id', $districtId);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $people = $query->with(['district', 'taluka', 'village', 'roles'])
            ->withCount(['applications', 'assignedApplications'])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Roles & Permissions matrix
        $rolesWithPermissions = Role::with('permissions')->get();
        $allPermissions = Permission::orderBy('name')->get();

        $districts = District::where('is_active', true)->with('talukas.villages')->get(['id', 'name_en', 'name_gu']);

        return Inertia::render('admin/people/index', [
            'people' => $people,
            'selectedRole' => $selectedRole,
            'search' => $search,
            'districtId' => $districtId,
            'rolesWithPermissions' => $rolesWithPermissions,
            'allPermissions' => $allPermissions,
            'districts' => $districts,
        ]);
    }

    /**
     * Create a new team member (Staff, Mentor, Partner, etc.) with geographic scope.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'role' => ['required', 'string', 'in:admin,staff,mentor,partner,volunteer,citizen'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'exists:talukas,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password'] ?? 'Secret123!'),
            'district_id' => $validated['district_id'] ?? null,
            'taluka_id' => $validated['taluka_id'] ?? null,
            'village_id' => $validated['village_id'] ?? null,
            'locale' => 'gu',
            'is_active' => true,
        ]);

        $newUser->assignRole($validated['role']);

        AuditLog::record(
            action: 'people.create',
            subject: $newUser,
            before: null,
            after: ['name' => $newUser->name, 'role' => $validated['role'], 'district_id' => $newUser->district_id],
            actorId: $request->user()?->id
        );

        return back()->with('success', "Added {$newUser->name} as {$validated['role']}.");
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        AuditLog::record(
            action: 'people.toggle_active',
            subject: $user,
            before: ['is_active' => ! $user->is_active],
            after: ['is_active' => $user->is_active],
            actorId: $request->user()?->id
        );

        return back()->with('success', "Updated active status for {$user->name}.");
    }
}
