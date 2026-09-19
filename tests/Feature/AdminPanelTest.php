<?php

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\WorkflowTransition;
use App\Domains\Content\Models\Category;
use App\Domains\Settings\Models\AuditLog;
use App\Domains\Users\Models\District;
use App\Models\User;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(ThemeAndLocalizationSeeder::class);

    $adminRole = Role::findOrCreate('admin');
    $staffRole = Role::findOrCreate('staff');
    Role::findOrCreate('citizen');
    Role::findOrCreate('mentor');

    // Assign full permissions
    $permissions = [
        'view cases',
        'update cases',
        'assign cases',
        'manage people',
        'manage roles',
        'view audit logs',
        'manage content',
    ];

    foreach ($permissions as $p) {
        $perm = Permission::findOrCreate($p);
        $adminRole->givePermissionTo($perm);
        if ($p !== 'manage roles') {
            $staffRole->givePermissionTo($perm);
        }
    }
});

test('unauthenticated user cannot access admin dashboard', function () {
    $response = $this->get('/admin/dashboard');
    $response->assertRedirect('/login');
});

test('admin dashboard loads metrics, urgent queue and breakdown stats', function () {
    $admin = User::factory()->create(['phone' => '9998880001']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/dashboard/index')
        ->has('metrics')
        ->has('urgent_cases')
        ->has('recent_timeline')
        ->has('category_breakdown')
        ->has('district_breakdown')
    );
});

test('cases index loads both in table and kanban modes with filtering', function () {
    $staff = User::factory()->create(['phone' => '9998880002']);
    $staff->assignRole('staff');

    // Table view
    $response = $this->actingAs($staff)->get('/admin/cases?view=table&status=all');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/cases/index')
        ->where('view', 'table')
        ->has('applications')
        ->has('status_counts')
    );

    // Kanban view
    $kanbanResponse = $this->actingAs($staff)->get('/admin/cases?view=kanban');
    $kanbanResponse->assertOk();
    $kanbanResponse->assertInertia(fn ($page) => $page
        ->component('admin/cases/index')
        ->where('view', 'kanban')
    );
});

test('case detail page shows application, citizen details, timeline and allowed transitions', function () {
    $admin = User::factory()->create(['phone' => '9998880003']);
    $admin->assignRole('admin');

    $citizen = User::factory()->create(['phone' => '9998880004']);
    $citizen->assignRole('citizen');

    $category = Category::create([
        'slug' => 'farm-equipment',
        'icon' => 'tractor',
        'is_active' => true,
    ]);

    $app = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Tractor subsidy request',
        'description' => 'Need subsidy for small tractor.',
        'status' => Application::STATUS_RECEIVED,
        'urgency' => 'normal',
        'priority' => 'medium',
    ]);

    WorkflowTransition::create([
        'from_status' => Application::STATUS_RECEIVED,
        'to_status' => Application::STATUS_VERIFICATION,
        'requires_note' => false,
        'allowed_roles' => ['admin', 'staff'],
    ]);

    $response = $this->actingAs($admin)->get("/admin/cases/{$app->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/cases/show')
        ->has('application')
        ->where('application.id', $app->id)
        ->has('allowedTransitions')
    );
});

test('admin can transition case workflow status and logs timeline and audit', function () {
    $admin = User::factory()->create(['phone' => '9998880005']);
    $admin->assignRole('admin');

    $citizen = User::factory()->create(['phone' => '9998880006']);
    $citizen->assignRole('citizen');

    $category = Category::create([
        'slug' => 'health-support',
        'icon' => 'heart',
        'is_active' => true,
    ]);

    $app = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Medical checkup help',
        'description' => 'Health checkup needed for elder.',
        'status' => Application::STATUS_RECEIVED,
        'urgency' => 'urgent',
        'priority' => 'high',
    ]);

    WorkflowTransition::create([
        'from_status' => Application::STATUS_RECEIVED,
        'to_status' => Application::STATUS_VERIFICATION,
        'requires_note' => false,
        'allowed_roles' => ['admin', 'staff'],
    ]);

    $response = $this->actingAs($admin)->post("/admin/cases/{$app->id}/transition", [
        'to_status' => Application::STATUS_VERIFICATION,
        'note' => 'Starting initial document verification',
    ]);

    $response->assertRedirect();
    $app->refresh();
    expect($app->status)->toBe(Application::STATUS_VERIFICATION);

    // Verify timeline entry created
    expect($app->timelineEvents()->where('to_status', Application::STATUS_VERIFICATION)->exists())->toBeTrue();

    // Verify audit log created
    expect(AuditLog::where('subject_id', $app->id)->where('action', 'case.transition.verification')->exists())->toBeTrue();
});

test('admin can add internal note not visible to citizen', function () {
    $admin = User::factory()->create(['phone' => '9998880007']);
    $admin->assignRole('admin');

    $citizen = User::factory()->create(['phone' => '9998880008']);
    $citizen->assignRole('citizen');

    $category = Category::create([
        'slug' => 'legal-aid',
        'icon' => 'scale',
        'is_active' => true,
    ]);

    $app = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Land boundary issue',
        'description' => 'Dispute with boundary markers.',
        'status' => Application::STATUS_VERIFICATION,
    ]);

    $response = $this->actingAs($admin)->post("/admin/cases/{$app->id}/note", [
        'note' => 'Spoke with local sarpanch privately.',
    ]);

    $response->assertRedirect();

    $noteEvent = $app->timelineEvents()->where('event_type', 'internal_note_added')->first();
    expect($noteEvent)->not->toBeNull();
    expect($noteEvent->visibility)->toBe('internal');
    expect($noteEvent->body)->toBe('Spoke with local sarpanch privately.');
});

test('admin can reassign case to another staff member', function () {
    $admin = User::factory()->create(['phone' => '9998880009']);
    $admin->assignRole('admin');

    $staff1 = User::factory()->create(['name' => 'Staff One', 'phone' => '9998880010']);
    $staff1->assignRole('staff');

    $staff2 = User::factory()->create(['name' => 'Staff Two', 'phone' => '9998880011']);
    $staff2->assignRole('staff');

    $category = Category::create([
        'slug' => 'borewell-grant',
        'icon' => 'droplet',
        'is_active' => true,
    ]);

    $app = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $admin->id,
        'category_id' => $category->id,
        'title' => 'Water borewell drilling',
        'description' => 'Need drinking water borewell.',
        'status' => Application::STATUS_VERIFICATION,
        'current_assignee_id' => $staff1->id,
    ]);

    $response = $this->actingAs($admin)->post("/admin/cases/{$app->id}/assign", [
        'assignee_id' => $staff2->id,
        'reason' => 'Workload redistribution',
    ]);

    $response->assertRedirect();
    $app->refresh();
    expect($app->current_assignee_id)->toBe($staff2->id);

    // Verify timeline and audit
    expect($app->timelineEvents()->where('event_type', 'case_reassigned')->exists())->toBeTrue();
    expect(AuditLog::where('subject_id', $app->id)->where('action', 'case.assign')->exists())->toBeTrue();
});

test('people management lists directory and creates new staff member with district scope', function () {
    $admin = User::factory()->create(['phone' => '9998880012']);
    $admin->assignRole('admin');

    $district = District::create([
        'name_en' => 'Dang',
        'name_gu' => 'ડાંગ',
        'code' => 'DNG',
        'is_tribal_majority' => true,
    ]);

    // Index view
    $indexResponse = $this->actingAs($admin)->get('/admin/people?tab=staff');
    $indexResponse->assertOk();
    $indexResponse->assertInertia(fn ($page) => $page
        ->component('admin/people/index')
        ->has('people')
        ->has('rolesWithPermissions')
        ->has('districts')
    );

    // Store new staff
    $storeResponse = $this->actingAs($admin)->post('/admin/people', [
        'name' => 'New Tribal Officer',
        'email' => 'officer@ggvt.org',
        'phone' => '9998880013',
        'password' => 'Password123!',
        'role' => 'staff',
        'district_id' => $district->id,
    ]);

    $storeResponse->assertRedirect();

    $newStaff = User::where('email', 'officer@ggvt.org')->first();
    expect($newStaff)->not->toBeNull();
    expect($newStaff->hasRole('staff'))->toBeTrue();
    expect($newStaff->district_id)->toBe($district->id);

    // Verify audit log
    expect(AuditLog::where('subject_id', $newStaff->id)->where('action', 'people.create')->exists())->toBeTrue();
});

test('content modules index displays registry of 8 welfare modules', function () {
    $admin = User::factory()->create(['phone' => '9998880014']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/content');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/index')
        ->has('modules', 8)
    );
});

test('audit log index displays paginated audit entries with action filtering', function () {
    $admin = User::factory()->create(['phone' => '9998880015']);
    $admin->assignRole('admin');

    AuditLog::create([
        'user_id' => $admin->id,
        'action' => 'security_audit',
        'subject_type' => 'App\Models\User',
        'subject_id' => $admin->id,
        'before' => ['status' => 'old'],
        'after' => ['status' => 'new'],
        'ip_address' => '127.0.0.1',
    ]);

    $response = $this->actingAs($admin)->get('/admin/audit?action=security_audit');
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/audit/index')
        ->has('logs.data', 1)
        ->where('selectedAction', 'security_audit')
    );
});
