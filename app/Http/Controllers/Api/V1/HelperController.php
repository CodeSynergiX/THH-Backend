<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationAssignment;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Requests\TransitionCaseRequest;
use App\Domains\Cases\Resources\ApplicationResource;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\MentorQuestion;
use App\Domains\Users\Scopes\ScopeHelper;
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
        $deskWide = $user->hasRole(['super_admin', 'admin', 'staff', 'collector']);
        $caseQuery = $deskWide
            ? ScopeHelper::applyApplicationScope(Application::query(), $user)
            : Application::where('current_assignee_id', $user->id);

        $assignedCount = (clone $caseQuery)
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->count();

        $inProgressCount = (clone $caseQuery)
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->count();

        $resolvedCount = (clone $caseQuery)
            ->where('status', Application::STATUS_RESOLVED)
            ->count();

        $pendingQuestionsCount = MentorQuestion::where('mentor_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $followUpsDueToday = FollowUp::where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->whereDate('scheduled_for', '<=', now()->toDateString())
            ->count();

        $slaBreachedCount = (clone $caseQuery)
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->count();

        $urgency = [
            'urgent' => (clone $caseQuery)->where('urgency', 'urgent')->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])->count(),
            'medium' => (clone $caseQuery)->where('urgency', 'medium')->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])->count(),
            'low' => (clone $caseQuery)->where('urgency', 'low')->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])->count(),
        ];

        $activeEmergencySos = BloodRequest::with('hospital')
            ->where('status', 'active')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Helper metrics loaded.',
            'data' => [
                'assigned_active_cases' => $assignedCount,
                'in_progress_cases' => $inProgressCount,
                'resolved_cases' => $resolvedCount,
                'pending_mentor_questions' => $pendingQuestionsCount,
                'follow_ups_due_today' => $followUpsDueToday,
                'sla_breached_cases' => $slaBreachedCount,
                'awaiting_confirmation' => (clone $caseQuery)->where('status', Application::STATUS_AWAITING_CONFIRMATION)->count(),
                'urgency' => $urgency,
                'emergency_sos' => $activeEmergencySos,
                'helper_status' => $user->helper_status,
                'on_duty' => (bool) $user->on_duty,
            ],
        ]);
    }

    /**
     * Get cases assigned to this helper.
     */
    public function cases(Request $request): JsonResponse
    {
        $user = $request->user();

        $casesQuery = Application::with(['user', 'category', 'subCategory', 'village.taluka.district', 'timelineEvents']);
        if ($user->isApprovedHelper() || $user->hasRole(['mentor', 'volunteer', 'staff', 'admin', 'super_admin', 'collector'])) {
            $casesQuery->where('current_assignee_id', $user->id);
        } else {
            $casesQuery->where('current_assignee_id', $user->id);
        }

        $cases = $casesQuery
            ->orderByRaw("CASE WHEN urgency = 'urgent' THEN 1 WHEN urgency = 'medium' THEN 2 ELSE 3 END")
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

        if ($application->status === Application::STATUS_ASSIGNED) {
            $application = $this->workflowService->transition(
                application: $application,
                toStatus: Application::STATUS_ASSISTANCE,
                actor: $user,
                note: 'Assignment accepted. Helper is proceeding.',
                visibility: 'public'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Case assignment accepted.',
            'data' => new ApplicationResource($application->fresh(['category', 'village'])),
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
        $application = Application::where('current_assignee_id', $user->id)->findOrFail($id);

        if ($request->filled('proof_photo')) {
            $application->documents()->create([
                'type' => 'field_visit_proof',
                'path' => $request->validated('proof_photo'),
                'status' => 'verified',
                'verified_by' => $user->id,
            ]);
        }

        if ($request->filled('urgency')) {
            $application->update(['urgency' => $request->validated('urgency')]);
        }

        if ($request->filled('lat') && $request->filled('lng')) {
            $application->update([
                'lat' => $request->validated('lat'),
                'lng' => $request->validated('lng'),
            ]);
        }

        $rawStatus = $request->validated('status');
        $statusAliases = [
            'in_verification' => Application::STATUS_VERIFICATION,
            'field_visit_completed' => Application::STATUS_ASSISTANCE,
            'field_visit' => Application::STATUS_ASSISTANCE,
            'awaiting_docs' => Application::STATUS_NEED_MORE_INFO,
            'need_more_info' => Application::STATUS_NEED_MORE_INFO,
            'approved' => Application::STATUS_ASSISTANCE,
            'solved' => Application::STATUS_AWAITING_CONFIRMATION,
            'on_hold' => Application::STATUS_ON_HOLD,
            'onHold' => Application::STATUS_ON_HOLD,
            'closed' => Application::STATUS_RESOLVED,
            'rejected' => Application::STATUS_REJECTED,
        ];
        $toStatus = $statusAliases[$rawStatus] ?? $rawStatus;
        $note = $request->validated('note') ?? $request->input('notes') ?? 'Case status updated by mentor.';

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: $toStatus,
            actor: $user,
            note: $note,
            visibility: $request->validated('visibility') ?? 'public'
        );

        return response()->json([
            'success' => true,
            'message' => "Application transitioned to {$updated->status}.",
            'data' => new ApplicationResource($updated->load(['category', 'village', 'timelineEvents', 'documents'])),
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

        $note = $request->input('resolution_note') ?? $request->input('notes') ?? $request->input('note');
        if (empty($note) || strlen(trim($note)) < 5) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a resolution note (minimum 5 characters).',
                'errors' => ['resolution_note' => ['The resolution note field must be at least 5 characters.']],
            ], 422);
        }

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: Application::STATUS_AWAITING_CONFIRMATION,
            actor: $user,
            note: trim($note),
            visibility: 'public'
        );

        return response()->json([
            'success' => true,
            'message' => 'Waiting for the citizen to confirm the case is resolved.',
            'data' => new ApplicationResource($updated),
        ]);
    }

    public function setDuty(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasRole(['mentor', 'volunteer', 'staff', 'collector', 'admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only field helpers can toggle duty status.',
            ], 403);
        }

        $validated = $request->validate([
            'on_duty' => ['required', 'boolean'],
        ]);

        $user->update(['on_duty' => $validated['on_duty']]);

        return response()->json([
            'success' => true,
            'message' => $validated['on_duty'] ? 'You are now on duty.' : 'You are now off duty.',
            'data' => [
                'on_duty' => (bool) $user->fresh()->on_duty,
                'helper_status' => $user->helper_status,
            ],
        ]);
    }
}
