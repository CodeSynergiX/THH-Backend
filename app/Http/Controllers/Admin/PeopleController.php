<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Users\Models\District;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'role' => ['required', 'string', 'in:admin,staff,mentor,partner,volunteer,citizen'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'exists:talukas,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:8'],
        ]);

        $first = $validated['first_name'] ?? null;
        $last = $validated['last_name'] ?? null;
        $name = $validated['name'] ?? trim(implode(' ', array_filter([$first, $last])));
        $isSevak = in_array($validated['role'], ['mentor', 'volunteer'], true);

        $newUser = new User([
            'name' => $name !== '' ? $name : $validated['email'],
            'first_name' => $first,
            'last_name' => $last,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'] ?? 'Secret123!',
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'blood_group' => $validated['blood_group'] ?? null,
            'district_id' => $validated['district_id'] ?? null,
            'taluka_id' => $validated['taluka_id'] ?? null,
            'village_id' => $validated['village_id'] ?? null,
            'locale' => 'gu',
            'is_active' => true,
            'helper_status' => $isSevak ? User::HELPER_APPROVED : null,
            'on_duty' => false,
        ]);
        if ($first || $last) {
            $newUser->syncDisplayName($first, $last);
        }
        $newUser->save();

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

    public function updateHelperStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'helper_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $user = User::findOrFail($id);
        $before = $user->helper_status;
        $user->helper_status = $validated['helper_status'];
        $user->save();

        AuditLog::record(
            action: 'people.helper_status',
            subject: $user,
            before: ['helper_status' => $before],
            after: ['helper_status' => $user->helper_status],
            actorId: $request->user()?->id
        );

        return back()->with('success', "Updated Sevak status for {$user->name}.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'blood_group' => ['nullable', 'string', 'max:8'],
        ]);

        $user->fill($validated);
        $user->syncDisplayName(
            $validated['first_name'] ?? $user->first_name,
            $validated['last_name'] ?? $user->last_name
        );
        $user->save();

        return back()->with('success', "Updated profile fields for {$user->name}.");
    }

    /**
     * Toggle a single permission on a role. Super admin cannot be stripped.
     */
    public function syncPermissions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string'],
            'permission' => ['required', 'string'],
            'granted' => ['required', 'boolean'],
        ]);

        if ($validated['role'] === 'super_admin') {
            return back()->with('error', 'Super admin permissions cannot be changed.');
        }

        $permissionName = $validated['permission'];

        foreach (['web', 'sanctum'] as $guard) {
            $role = Role::findByName($validated['role'], $guard);
            Permission::findOrCreate($permissionName, $guard);

            if ($validated['granted']) {
                $role->givePermissionTo($permissionName);
            } else {
                $role->revokePermissionTo($permissionName);
            }
        }

        AuditLog::record(
            action: 'roles.permission.toggle',
            subject: $request->user(),
            before: null,
            after: [
                'role' => $validated['role'],
                'permission' => $permissionName,
                'granted' => $validated['granted'],
            ],
            actorId: $request->user()?->id
        );

        return back()->with('success', "Updated {$validated['role']} permission: {$permissionName}.");
    }
}
