<?php

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\WorkflowTransition;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Content\Models\Category;
use App\Domains\Settings\Models\AuditLog;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('citizen');
    Role::findOrCreate('staff');
    Role::findOrCreate('admin');
});

test('submitting application generates correct THH-{YYYY}-{00001} format and timeline entry', function () {
    $citizen = User::factory()->create(['phone' => '9876543201']);
    $citizen->assignRole('citizen');

    $category = Category::create([
        'slug' => 'education-grant',
        'icon' => 'book',
        'is_active' => true,
    ]);

    $response = $this->actingAs($citizen, 'sanctum')->postJson('/api/v1/applications', [
        'category_id' => $category->id,
        'title' => 'College Scholarship Assistance',
        'description' => 'Requesting financial aid for engineering tuition fees.',
        'urgency' => 'normal',
    ]);

    $response->assertCreated();
    $caseNo = $response->json('data.case_no');

    $year = date('Y');
    expect($caseNo)->toMatch("/^THH-{$year}-\d{5}$/");

    $application = Application::where('case_no', $caseNo)->first();
    expect($application)->not->toBeNull();
    expect($application->status)->toBe(Application::STATUS_RECEIVED);
    expect($application->timelineEvents()->count())->toBe(1);
});

test('idempotency key prevents duplicate application creation', function () {
    $citizen = User::factory()->create(['phone' => '9876543202']);
    $citizen->assignRole('citizen');

    $category = Category::create([
        'slug' => 'housing-repair',
        'icon' => 'home',
        'is_active' => true,
    ]);

    $idempotencyKey = 'IDEM_'.uniqid();

    // First request
    $response1 = $this->actingAs($citizen, 'sanctum')
        ->withHeader('X-Idempotency-Key', $idempotencyKey)
        ->postJson('/api/v1/applications', [
            'category_id' => $category->id,
            'title' => 'Roof Repair Assistance',
            'description' => 'Tiled roof damaged during heavy monsoon rains.',
        ]);

    $response1->assertCreated();
    $id1 = $response1->json('data.id');

    // Duplicate request with identical idempotency key
    $response2 = $this->actingAs($citizen, 'sanctum')
        ->withHeader('X-Idempotency-Key', $idempotencyKey)
        ->postJson('/api/v1/applications', [
            'category_id' => $category->id,
            'title' => 'Roof Repair Assistance',
            'description' => 'Tiled roof damaged during heavy monsoon rains.',
        ]);

    $response2->assertOk();
    $id2 = $response2->json('data.id');

    expect($id1)->toBe($id2);
    expect(Application::where('idempotency_key', $idempotencyKey)->count())->toBe(1);
});

test('application workflow transitions update status, write timeline, audit log, and calculate SLA', function () {
    $citizen = User::factory()->create(['phone' => '9876543203']);
    $citizen->assignRole('citizen');

    $staff = User::factory()->create(['phone' => '9876543204']);
    $staff->assignRole('staff');

    $category = Category::create([
        'slug' => 'legal-aid',
        'icon' => 'scale',
        'is_active' => true,
    ]);

    // Setup transition rules
    WorkflowTransition::create([
        'from_status' => Application::STATUS_RECEIVED,
        'to_status' => Application::STATUS_VERIFICATION,
        'allowed_roles' => ['staff', 'admin'],
        'requires_note' => false,
    ]);

    $application = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Land Dispute Guidance',
        'description' => 'Need advice regarding village land title ownership dispute.',
        'urgency' => 'urgent',
        'priority' => 'high',
        'status' => Application::STATUS_RECEIVED,
    ]);

    /** @var ApplicationWorkflowService $workflow */
    $workflow = app(ApplicationWorkflowService::class);

    $updated = $workflow->transition(
        application: $application,
        toStatus: Application::STATUS_VERIFICATION,
        actor: $staff,
        note: 'Document verification initiated by taluka office.'
    );

    expect($updated->status)->toBe(Application::STATUS_VERIFICATION);
    expect($updated->sla_due_at)->not->toBeNull();

    // Verify timeline event
    $latestEvent = $updated->timelineEvents()->latest('id')->first();
    expect($latestEvent->from_status)->toBe(Application::STATUS_RECEIVED);
    expect($latestEvent->to_status)->toBe(Application::STATUS_VERIFICATION);
    expect($latestEvent->body)->toBe('Document verification initiated by taluka office.');

    // Verify audit log
    $auditLog = AuditLog::where('action', 'case.transition.verification')->first();
    expect($auditLog)->not->toBeNull();
    expect($auditLog->actor_id)->toBe($staff->id);
});

test('mentor can transition assigned case to awaiting_confirmation and update status', function () {
    Role::findOrCreate('mentor');

    $citizen = User::factory()->create(['phone' => '9876543205']);
    $citizen->assignRole('citizen');

    $mentor = User::factory()->create(['phone' => '9876543206', 'helper_status' => 'approved']);
    $mentor->assignRole('mentor');

    $category = Category::create([
        'slug' => 'health-support',
        'icon' => 'medkit',
        'is_active' => true,
    ]);

    $application = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Medical Assistance',
        'description' => 'Need help for hospital treatment.',
        'urgency' => 'urgent',
        'priority' => 'high',
        'status' => Application::STATUS_ASSIGNED,
        'current_assignee_id' => $mentor->id,
    ]);

    // Test resolving directly from assigned via Helper API
    $response = $this->actingAs($mentor, 'sanctum')->postJson("/api/v1/helper/cases/{$application->id}/resolve", [
        'resolution_note' => 'Field inspection conducted and medical grant processed.',
    ]);

    $response->assertOk();
    expect($application->fresh()->status)->toBe(Application::STATUS_AWAITING_CONFIRMATION);

    // Timeline should have recorded intermediate assistance and awaiting_confirmation
    expect($application->timelineEvents()->where('event_type', 'status_changed_assistance')->exists())->toBeTrue();
    expect($application->timelineEvents()->where('event_type', 'status_changed_awaiting_confirmation')->exists())->toBeTrue();
});

test('mentor can transition case to onHold and resolved via helper status update', function () {
    Role::findOrCreate('mentor');

    $citizen = User::factory()->create(['phone' => '9876543207']);
    $citizen->assignRole('citizen');

    $mentor = User::factory()->create(['phone' => '9876543208', 'helper_status' => 'approved']);
    $mentor->assignRole('mentor');

    $category = Category::create([
        'slug' => 'agri-grant',
        'icon' => 'leaf',
        'is_active' => true,
    ]);

    $application = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Crop Assistance',
        'description' => 'Crop damaged by wild animals.',
        'urgency' => 'medium',
        'priority' => 'normal',
        'status' => Application::STATUS_ASSIGNED,
        'current_assignee_id' => $mentor->id,
    ]);

    // 1. Transition to onHold
    $responseOnHold = $this->actingAs($mentor, 'sanctum')->postJson("/api/v1/helper/cases/{$application->id}/status", [
        'status' => 'onHold',
        'notes' => 'Awaiting forest department clearance report.',
    ]);
    $responseOnHold->assertOk();
    expect($application->fresh()->status)->toBe(Application::STATUS_ON_HOLD);

    // 2. Transition from onHold to resolved
    $responseResolved = $this->actingAs($mentor, 'sanctum')->postJson("/api/v1/helper/cases/{$application->id}/status", [
        'status' => 'resolved',
        'notes' => 'Clearance received and compensation disbursed.',
    ]);
    $responseResolved->assertOk();
    expect($application->fresh()->status)->toBe(Application::STATUS_RESOLVED);
    expect($application->fresh()->resolved_at)->not->toBeNull();
});
