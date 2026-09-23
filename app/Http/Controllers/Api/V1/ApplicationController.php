<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationDocument;
use App\Domains\Cases\Models\ApplicationMessage;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Requests\StoreApplicationRequest;
use App\Domains\Cases\Resources\ApplicationDocumentResource;
use App\Domains\Cases\Resources\ApplicationResource;
use App\Domains\Cases\Resources\ApplicationTimelineResource;
use App\Domains\Cases\Resources\CitizenApplicationResource;
use App\Domains\Cases\Services\ApplicationSubmissionService;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Cases\Services\AutoAssignmentService;
use App\Domains\Cases\Services\SLAEngineService;
use App\Domains\Cases\Urgency;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Domains\Users\Services\OtpService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationWorkflowService $workflowService,
        protected SLAEngineService $slaEngine,
        protected AutoAssignmentService $assignmentService,
        protected ApplicationSubmissionService $submissionService,
        protected OtpService $otp
    ) {}

    /**
     * List applications scoped by role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Application::with(['category', 'subCategory', 'village.taluka.district', 'currentAssignee', 'user', 'timelineEvents', 'documents']);
        $query = ScopeHelper::applyApplicationScope($query, $user);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', Urgency::normalize((string) $request->query('urgency')));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('case_no', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($user->hasRole('citizen') && ! $user->hasRole(['admin', 'super_admin', 'staff', 'collector', 'mentor', 'volunteer'])) {
            $applications = $query->orderBy('created_at', 'desc')->paginate(15);
        } else {
            $applications = $query
                ->orderByRaw("CASE WHEN urgency = 'urgent' THEN 1 WHEN urgency = 'medium' THEN 2 ELSE 3 END")
                ->orderBy('sla_due_at')
                ->paginate(15);
        }

        $resourceClass = $user->hasRole('citizen')
            ? CitizenApplicationResource::class
            : ApplicationResource::class;

        return response()->json([
            'success' => true,
            'message' => 'Applications retrieved.',
            'data' => $resourceClass::collection($applications),
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    /**
     * Submit a new help request.
     */
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $resolved = $this->submissionService->resolveCitizen(
            $request->user(),
            $request->validated()
        );

        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->validated('idempotency_key');
        $replayed = $idempotencyKey
            ? Application::query()->where('idempotency_key', $idempotencyKey)->exists()
            : false;

        $application = $this->submissionService->submit(
            $resolved['user'],
            $request->validated(),
            $idempotencyKey,
            $resolved['created'],
            $resolved['password']
        );

        return response()->json([
            'success' => true,
            'message' => 'Help request registered successfully with Case ID '.$application->case_no,
            'data' => new CitizenApplicationResource($application->load(['category', 'village', 'timelineEvents'])),
            'account_created' => $resolved['created'],
        ], $replayed ? 200 : 201);
    }

    /**
     * Get details of a single application.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $query = Application::with(['category', 'subCategory', 'village.taluka.district', 'currentAssignee', 'documents', 'timelineEvents']);
        $query = ScopeHelper::applyApplicationScope($query, $user);

        $application = $query->find($id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found or unauthorized access.',
            ], 404);
        }

        $resource = $user->hasRole('citizen')
            ? new CitizenApplicationResource($application)
            : new ApplicationResource($application);

        return response()->json([
            'success' => true,
            'message' => 'Application details loaded.',
            'data' => $resource,
        ]);
    }

    /**
     * Get application timeline.
     * Note: Citizens ONLY see public events. Staff / mentors see public + internal events!
     */
    public function timeline(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->find($id);
        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.',
            ], 404);
        }

        $eventsQuery = $application->timelineEvents();

        // CRITICAL ENFORCEMENT: Never return internal events or notes to citizens!
        if ($user->hasRole('citizen')) {
            $eventsQuery->where('visibility', 'public');
        }

        $events = $eventsQuery->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Timeline events retrieved.',
            'data' => ApplicationTimelineResource::collection($events),
        ]);
    }

    /**
     * Upload an attachment / document for the application.
     */
    public function uploadDocument(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'file' => ['required', 'file', 'max:10240'], // 10MB limit
        ]);

        $path = $request->file('file')->store("applications/{$application->id}", 'public');

        $doc = ApplicationDocument::create([
            'application_id' => $application->id,
            'type' => $validated['type'],
            'path' => $path,
            'status' => 'pending',
        ]);

        // Add timeline event
        $application->timelineEvents()->create([
            'event_type' => 'document_uploaded',
            'title_key' => 'app.timeline.document_uploaded',
            'body' => "Uploaded document: {$validated['type']}",
            'actor_id' => $user->id,
            'actor_role' => $user->getRoleNames()->first() ?? 'citizen',
            'visibility' => 'public',
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'data' => new ApplicationDocumentResource($doc),
        ], 201);
    }

    /**
     * Get chat messages for application.
     */
    public function messages(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $messages = $application->messages()->with('sender')->get();

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved.',
            'data' => $messages,
        ]);
    }

    /**
     * Send a message on the application.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'attachments' => ['nullable', 'array'],
        ]);

        $msg = ApplicationMessage::create([
            'application_id' => $application->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'attachments' => $validated['attachments'] ?? null,
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
            'data' => $msg->load('sender'),
        ], 201);
    }

    /**
     * Citizen confirms the desk closed the case.
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::where('user_id', $user->id)->findOrFail($id);

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: Application::STATUS_RESOLVED,
            actor: $user,
            note: $request->input('note', 'Citizen confirmed the case is resolved.'),
            visibility: 'public'
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you. The case is now closed.',
            'data' => new CitizenApplicationResource($updated),
        ]);
    }

    /**
     * Reopen a resolved application.
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $updated = $this->workflowService->transition(
            application: $application,
            toStatus: Application::STATUS_REOPENED,
            actor: $user,
            note: $validated['reason'],
            visibility: 'public'
        );

        return response()->json([
            'success' => true,
            'message' => 'Application has been reopened.',
            'data' => new CitizenApplicationResource($updated),
        ]);
    }

    /**
     * Submit rating and feedback for a resolved application.
     */
    public function feedback(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $application = Application::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'feedback' => ['nullable', 'string', 'max:500'],
        ]);

        $application->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully.',
            'data' => $application->only(['rating', 'feedback']),
        ]);
    }

    /**
     * Appointments (follow-ups) scoped by role.
     */
    public function appointments(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = FollowUp::query()->with(['application:id,case_no,title,status,user_id', 'assignee:id,name']);

        if ($user->hasRole(['mentor', 'volunteer'])) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->hasRole('citizen') && ! $user->hasRole(['admin', 'super_admin', 'staff', 'collector'])) {
            $query->whereHas('application', fn ($q) => $q->where('user_id', $user->id));
        } elseif (! $user->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            $query->whereHas('application', fn ($q) => ScopeHelper::applyApplicationScope($q, $user));
        }

        $items = $query->orderBy('scheduled_for')->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Appointments loaded.',
            'data' => $items,
        ]);
    }

    /**
     * Public tracking by Case Number (e.g. THH-2026-00001).
     */
    public function requestTrackOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'case_no' => ['required', 'string'],
        ]);

        $application = Application::query()
            ->with('user')
            ->where('case_no', strtoupper(trim($validated['case_no'])))
            ->first();

        if (! $application?->user?->email) {
            return response()->json([
                'success' => false,
                'message' => 'No email is registered for this case.',
            ], 404);
        }

        $code = $this->otp->issue(null, $application->user->email, 'track');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to the application email.',
            'data' => [
                'email_hint' => $this->maskEmail($application->user->email),
                'debug_code' => null,
            ],
        ]);
    }

    public function track(Request $request, string $caseNo): JsonResponse
    {
        $application = Application::where('case_no', strtoupper(trim($caseNo)))
            ->with(['category', 'subCategory', 'village.taluka.district', 'user', 'publicTimelineEvents.actor:id,name'])
            ->first();

        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found with the specified Case Number.',
            ], 404);
        }

        $user = $request->user();
        $otp = $request->input('otp');

        $allowed = $user && (
            $user->id === $application->user_id
            || $user->hasRole(['super_admin', 'admin', 'staff', 'collector'])
            || $application->current_assignee_id === $user->id
        );

        if (! $allowed && $otp && $application->user?->email) {
            $allowed = $this->otp->verify(null, $application->user->email, (string) $otp, 'track');
        }

        if (! $allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Login or verify the application email OTP to view this case.',
                'requires_auth' => true,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new CitizenApplicationResource($application),
        ]);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return substr($name, 0, 2).'***@'.$domain;
    }
}
