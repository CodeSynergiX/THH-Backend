<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationDocument;
use App\Domains\Cases\Models\ApplicationMessage;
use App\Domains\Cases\Requests\StoreApplicationRequest;
use App\Domains\Cases\Resources\ApplicationDocumentResource;
use App\Domains\Cases\Resources\ApplicationResource;
use App\Domains\Cases\Resources\ApplicationTimelineResource;
use App\Domains\Cases\Resources\CitizenApplicationResource;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Cases\Services\AutoAssignmentService;
use App\Domains\Cases\Services\SLAEngineService;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function __construct(
        protected ApplicationWorkflowService $workflowService,
        protected SLAEngineService $slaEngine,
        protected AutoAssignmentService $assignmentService
    ) {}

    /**
     * List applications scoped by role.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Application::with(['category', 'subCategory', 'village.taluka.district', 'currentAssignee']);
        $query = ScopeHelper::applyApplicationScope($query, $user);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('case_no', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

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
        $user = $request->user();

        if (! $user) {
            $phone = $request->validated('phone') ?? $request->validated('beneficiary_phone') ?? ('98765'.rand(10000, 99999));
            $name = $request->validated('name') ?? $request->validated('beneficiary_name') ?? 'Tribal Citizen';
            $user = User::firstOrCreate(
                ['phone' => $phone],
                ['name' => $name, 'locale' => 'gu']
            );
            if (! $user->hasRole('citizen')) {
                $user->assignRole('citizen');
            }
        }

        $idempotencyKey = $request->header('X-Idempotency-Key') ?? $request->validated('idempotency_key');

        if ($idempotencyKey) {
            $existing = Application::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Application previously submitted.',
                    'data' => new CitizenApplicationResource($existing->load(['category', 'village'])),
                ]);
            }
        }

        $urgency = $request->validated('urgency') ?? 'normal';
        $priority = match ($urgency) {
            'critical' => 'critical',
            'urgent' => 'high',
            default => 'medium',
        };

        $application = DB::transaction(function () use ($request, $user, $urgency, $priority, $idempotencyKey) {
            $caseNo = Application::generateCaseNo();
            $slaDueAt = $this->slaEngine->calculateDueDate($urgency, $priority);

            $app = Application::create([
                'case_no' => $caseNo,
                'user_id' => $user->id,
                'category_id' => $request->validated('category_id'),
                'sub_category_id' => $request->validated('sub_category_id'),
                'title' => $request->validated('title'),
                'description' => $request->validated('description'),
                'urgency' => $urgency,
                'priority' => $priority,
                'status' => Application::STATUS_RECEIVED,
                'village_id' => $request->validated('village_id') ?? $user->village_id,
                'lat' => $request->validated('lat'),
                'lng' => $request->validated('lng'),
                'sla_due_at' => $slaDueAt,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Create initial timeline event
            $app->timelineEvents()->create([
                'event_type' => 'case_created',
                'from_status' => null,
                'to_status' => Application::STATUS_RECEIVED,
                'title_key' => 'app.timeline.case_received',
                'body' => 'Your help request has been registered and assigned case number '.$caseNo,
                'actor_id' => $user->id,
                'actor_role' => 'citizen',
                'visibility' => 'public',
                'created_at' => now(),
            ]);

            // Intelligent auto-assignment
            $assignedStaff = $this->assignmentService->assignBestStaff($app);
            if ($assignedStaff) {
                $app->update(['current_assignee_id' => $assignedStaff->id]);
                $app->assignments()->create([
                    'assignee_id' => $assignedStaff->id,
                    'assignee_type' => 'staff',
                    'assigned_by' => $user->id,
                    'created_at' => now(),
                ]);
            }

            return $app;
        });

        return response()->json([
            'success' => true,
            'message' => 'Help request registered successfully with Case ID '.$application->case_no,
            'data' => new CitizenApplicationResource($application->load(['category', 'village', 'timelineEvents'])),
        ], 201);
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
     * Public tracking by Case Number (e.g. THH-2026-00001).
     */
    public function track(Request $request, string $caseNo): JsonResponse
    {
        $application = Application::where('case_no', strtoupper(trim($caseNo)))
            ->with(['category', 'subCategory', 'village.taluka.district', 'publicTimelineEvents.actor:id,name'])
            ->first();

        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found with the specified Case Number.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CitizenApplicationResource($application),
        ]);
    }
}
