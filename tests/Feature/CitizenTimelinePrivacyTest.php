<?php

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Content\Models\Category;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('citizen');
    Role::findOrCreate('staff');
    Role::findOrCreate('admin');
});

test('citizen-facing API resources strictly hide internal timeline events and internal notes', function () {
    $citizen = User::factory()->create(['phone' => '9876543210']);
    $citizen->assignRole('citizen');

    $staff = User::factory()->create(['phone' => '9876543211']);
    $staff->assignRole('staff');

    $category = Category::create([
        'slug' => 'health-emergency',
        'icon' => 'heart',
        'is_active' => true,
    ]);

    $application = Application::create([
        'case_no' => Application::generateCaseNo(),
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Urgent Medical Assistance Needed',
        'description' => 'Need help with hospital admission and financial aid.',
        'urgency' => 'urgent',
        'priority' => 'high',
        'status' => Application::STATUS_RECEIVED,
    ]);

    // 1. Add public timeline event
    ApplicationTimelineEvent::create([
        'application_id' => $application->id,
        'event_type' => 'case_created',
        'from_status' => null,
        'to_status' => Application::STATUS_RECEIVED,
        'title_key' => 'app.timeline.case_received',
        'body' => 'Your case has been received by staff.',
        'actor_id' => $staff->id,
        'actor_role' => 'staff',
        'visibility' => 'public',
        'created_at' => now(),
    ]);

    // 2. Add INTERNAL note via ApplicationWorkflowService (visibility = internal)
    /** @var ApplicationWorkflowService $workflow */
    $workflow = app(ApplicationWorkflowService::class);
    $workflow->addInternalNote(
        application: $application,
        actor: $staff,
        note: 'INTERNAL CONFIDENTIAL: Background verification shows family is eligible under tribal welfare fund.'
    );

    // Verify DB has 2 events (1 public, 1 internal)
    expect($application->timelineEvents()->count())->toBe(2);
    expect($application->timelineEvents()->where('visibility', 'internal')->count())->toBe(1);

    // 3. Request as Citizen: GET /api/v1/applications/{id}/timeline
    $response = $this->actingAs($citizen, 'sanctum')
        ->getJson("/api/v1/applications/{$application->id}/timeline");

    $response->assertOk();
    $data = $response->json('data');

    // Citizen MUST see exactly 1 event (the public one)
    expect($data)->toHaveCount(1);
    expect($data[0]['visibility'])->toBe('public');

    // Assert that the internal confidential text is NEVER in the citizen response JSON
    $response->assertDontSee('INTERNAL CONFIDENTIAL');
    $response->assertDontSee('tribal welfare fund');

    // 4. Request as Staff: GET /api/v1/applications/{id}/timeline
    $staffResponse = $this->actingAs($staff, 'sanctum')
        ->getJson("/api/v1/applications/{$application->id}/timeline");

    $staffResponse->assertOk();
    $staffData = $staffResponse->json('data');

    // Staff sees both public and internal events
    expect($staffData)->toHaveCount(2);
    $staffResponse->assertSee('INTERNAL CONFIDENTIAL');
});
