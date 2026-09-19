<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationAssignment;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Requests\TransitionCaseRequest;
use App\Domains\Cases\Resources\ApplicationResource;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Content\Models\MentorQuestion;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function __construct(
        protected ApplicationWorkflowService $workflowService
    ) {}

    /**
     * Helper dashboard KPIs (real database aggregations, never fake numbers).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $assignedCount = Application::where('current_assignee_id', $user->id)
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->count();

        $resolvedCount = Application::where('current_assignee_id', $user->id)
            ->where('status', Application::STATUS_RESOLVED)
            ->count();

        $pendingQuestionsCount = MentorQuestion::where('mentor_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $followUpsDueToday = FollowUp::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->whereDate('scheduled_for', '<=', now()->toDateString())
            ->count();

        $slaBreachedCount = Application::where('current_assignee_id', $user->id)
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Helper metrics loaded.',
            'data' => [
                'assigned_active_cases' => $assignedCount,
                'resolved_cases' => $resolvedCount,
                'pending_mentor_questions' => $pendingQuestionsCount,
                'follow_ups_due_today' => $followUpsDueToday,
                'sla_breached_cases' => $slaBreachedCount,
            ],
        ]);
    }

    /**
     * Get cases assigned to this helper.
     */
    public function cases(Request $request): JsonResponse
    {
        $user = $request->user();

        $cases = Application::with(['user', 'category', 'subCategory', 'village.taluka.district'])
            ->where('current_assignee_id', $user->id)
            ->orderByRaw("CASE WHEN urgency = 'critical' THEN 1 WHEN urgency = 'urgent' THEN 2 ELSE 3 END")
            ->orderBy('sla_due_at', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Assigned cases loaded.',
            'data' => ApplicationResource::collection($cases),
            'meta' => [
                'current_page' => $cases->currentPage(),
                'last_page' => $cases->lastPage(),
                'total' => $cases->total(),
            ],
        ]);
    }

    /**
     * Accept a case assignment.
     */
    public function acceptCase(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::where('current_assignee_id', $user->id)->findOrFail($id);

        ApplicationAssignment::where('application_id', $application->id)
            ->where('assignee_id', $user->id)
            ->update(['accepted_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Case assignment accepted.',
            'data' => new ApplicationResource($application),
        ]);
    }

    /**
     * Decline a case assignment with reason.
     */
    public function declineCase(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::where('current_assignee_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        ApplicationAssignment::where('application_id', $application->id)
            ->where('assignee_id', $user->id)
            ->update([
                'declined_at' => now(),
                'reason' => $validated['reason'],
            ]);

        $application->update(['current_assignee_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Case assignment declined.',
            'data' => null,
        ]);
    }

    /**
     * Update case status via ApplicationWorkflowService.
     */
    public function updateStatus(TransitionCaseRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::findOrFail($id);

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: $request->validated('status'),
            actor: $user,
            note: $request->validated('note'),
            visibility: $request->validated('visibility') ?? 'public'
        );

        return response()->json([
            'success' => true,
            'message' => "Application transitioned to {$updated->status}.",
            'data' => new ApplicationResource($updated->load(['category', 'village', 'timelineEvents'])),
        ]);
    }

    /**
     * Add an internal note (visible only to staff/mentors, never citizens).
     */
    public function addNote(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'note' => ['required', 'string', 'min:3'],
        ]);

        $event = $this->workflowService->addInternalNote(
            application: $application,
            actor: $user,
            note: $validated['note']
        );

        return response()->json([
            'success' => true,
            'message' => 'Internal note added.',
            'data' => $event,
        ], 201);
    }

    /**
     * Request more info from citizen.
     */
    public function requestInfo(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:5'],
        ]);

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: Application::STATUS_NEED_MORE_INFO,
            actor: $user,
            note: $validated['message'],
            visibility: 'public'
        );

        return response()->json([
            'success' => true,
            'message' => 'Information requested from citizen.',
            'data' => new ApplicationResource($updated),
        ]);
    }

    /**
     * Schedule a follow-up.
     */
    public function scheduleFollowUp(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'scheduled_for' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string'],
        ]);

        $followUp = FollowUp::create([
            'application_id' => $application->id,
            'scheduled_for' => $validated['scheduled_for'],
            'assigned_to' => $user->id,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up scheduled.',
            'data' => $followUp,
        ], 201);
    }

    /**
     * Resolve the case.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::findOrFail($id);

        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'min:10'],
        ]);

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: Application::STATUS_RESOLVED,
            actor: $user,
            note: $validated['resolution_note'],
            visibility: 'public'
        );

        return response()->json([
            'success' => true,
            'message' => 'Case resolved successfully.',
            'data' => new ApplicationResource($updated),
        ]);
    }
}
