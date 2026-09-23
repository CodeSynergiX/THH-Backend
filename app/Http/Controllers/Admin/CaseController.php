<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationAssignment;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Cases\Services\SLAEngineService;
use App\Domains\Content\Models\Category;
use App\Domains\Notifications\Services\NotificationDispatcherService;
use App\Domains\Settings\Models\AuditLog;
use App\Domains\Users\Models\District;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CaseController extends Controller
{
    public function __construct(
        protected ApplicationWorkflowService $workflow,
        protected NotificationDispatcherService $notifier
    ) {}

    /**
     * Applications Workspace: Table or Kanban view.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $view = $request->query('view', 'table');
        $status = $request->query('status', 'all');
        $urgency = $request->query('urgency', 'all');
        $categoryId = $request->query('category_id', 'all');
        $districtId = $request->query('district_id', 'all');
        $search = $request->query('search', '');

        $query = ScopeHelper::applyApplicationScope(Application::query(), $user);

        // Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Urgency filter
        if ($urgency !== 'all') {
            $query->where('urgency', $urgency);
        }

        // Category filter
        if ($categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        // District filter
        if ($districtId !== 'all') {
            $query->whereHas('village.taluka', function ($q) use ($districtId) {
                $q->where('district_id', $districtId);
            });
        }

        // Search query
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('case_no', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $query->with(['category', 'village.taluka.district', 'currentAssignee', 'user:id,name,phone']);

        // Fetch applications
        $applications = $view === 'kanban'
            ? $query->orderBy('created_at', 'desc')->get()
            : $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Status Counts for Filter Badges
        $countsQuery = ScopeHelper::applyApplicationScope(Application::query(), $user);
        $statusCounts = [
            'all' => (clone $countsQuery)->count(),
            Application::STATUS_RECEIVED => (clone $countsQuery)->where('status', Application::STATUS_RECEIVED)->count(),
            Application::STATUS_VERIFICATION => (clone $countsQuery)->where('status', Application::STATUS_VERIFICATION)->count(),
            Application::STATUS_CATEGORISED => (clone $countsQuery)->where('status', Application::STATUS_CATEGORISED)->count(),
            Application::STATUS_ASSIGNED => (clone $countsQuery)->where('status', Application::STATUS_ASSIGNED)->count(),
            Application::STATUS_ASSISTANCE => (clone $countsQuery)->where('status', Application::STATUS_ASSISTANCE)->count(),
            Application::STATUS_FOLLOW_UP => (clone $countsQuery)->where('status', Application::STATUS_FOLLOW_UP)->count(),
            Application::STATUS_AWAITING_CONFIRMATION => (clone $countsQuery)->where('status', Application::STATUS_AWAITING_CONFIRMATION)->count(),
            Application::STATUS_RESOLVED => (clone $countsQuery)->where('status', Application::STATUS_RESOLVED)->count(),
            Application::STATUS_REJECTED => (clone $countsQuery)->where('status', Application::STATUS_REJECTED)->count(),
            Application::STATUS_ON_HOLD => (clone $countsQuery)->where('status', Application::STATUS_ON_HOLD)->count(),
        ];

        $categories = Category::where('is_active', true)->get(['id', 'slug']);
        $districts = District::where('is_active', true)->get(['id', 'name_en', 'name_gu']);

        return Inertia::render('admin/cases/index', [
            'applications' => $applications,
            'view' => $view,
            'filters' => [
                'status' => $status,
                'urgency' => $urgency,
                'category_id' => $categoryId,
                'district_id' => $districtId,
                'search' => $search,
            ],
            'status_counts' => $statusCounts,
            'categories' => $categories,
            'districts' => $districts,
        ]);
    }

    /**
     * Case Detail view: Complete timeline, documents, communications, audit log diffs.
     */
    public function show(Request $request, int $id): Response
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)
            ->with([
                'user.district',
                'user.taluka',
                'user.village',
                'category',
                'subCategory',
                'village.taluka.district',
                'currentAssignee',
                'documents',
                'timelineEvents.actor:id,name',
                'assignments.assignee:id,name',
                'messages.sender:id,name',
                'followUps',
            ])
            ->findOrFail($id);

        // Fetch Audit Logs for this application
        $auditLogs = AuditLog::where('subject_type', Application::class)
            ->where('subject_id', $application->id)
            ->with('actor:id,name')
            ->orderBy('id', 'desc')
            ->get();

        // Available staff members for reassignment with active case count
        $availableStaff = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['staff', 'mentor', 'volunteer', 'collector']);
        })
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('helper_status')->orWhere('helper_status', User::HELPER_APPROVED);
            })
            ->withCount(['assignedApplications' => function ($q) {
                $q->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED]);
            }])
            ->get(['id', 'name', 'email']);

        // Eligible transitions based on current status
        $allStatuses = [
            Application::STATUS_RECEIVED => 'Received (નવી અરજી)',
            Application::STATUS_VERIFICATION => 'In Verification / Field Inspection (ચકાસણી હેઠળ)',
            Application::STATUS_CATEGORISED => 'Categorised (વર્ગીકૃત)',
            Application::STATUS_ASSIGNED => 'Assigned to Mentor (સેવકને સોંપેલ)',
            Application::STATUS_ASSISTANCE => 'Assistance In Progress (સહાય પ્રગતિમાં)',
            Application::STATUS_FOLLOW_UP => 'Follow-Up Scheduled (ફોલો-અપ શેડ્યૂલ)',
            Application::STATUS_AWAITING_CONFIRMATION => 'Awaiting Citizen Confirmation (અરજદાર પુષ્ટિ)',
            Application::STATUS_RESOLVED => 'Resolved / Closed (સફળતાપૂર્વક પૂર્ણ)',
            Application::STATUS_NEED_MORE_INFO => 'Need More Info (વધુ વિગત જરૂરી)',
            Application::STATUS_ON_HOLD => 'On Hold (મોકૂફ રાખેલ)',
            Application::STATUS_REJECTED => 'Rejected (અસ્વીકાર)',
        ];

        $allowedTransitions = collect($allStatuses)
            ->reject(fn ($label, $status) => $status === $application->status)
            ->map(fn ($label, $status) => [
                'from_status' => $application->status,
                'to_status' => $status,
                'label' => $label,
            ])
            ->values();

        return Inertia::render('admin/cases/show', [
            'application' => $application,
            'auditLogs' => $auditLogs,
            'availableStaff' => $availableStaff,
            'allowedTransitions' => $allowedTransitions,
        ]);
    }

    /**
     * Transition application to a new status.
     */
    public function transition(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'to_status' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['nullable', 'string', 'in:public,internal'],
        ]);

        $this->workflow->transition(
            application: $application,
            toStatus: $validated['to_status'],
            actor: $user,
            note: $validated['note'] ?? null,
            visibility: $validated['visibility'] ?? 'public'
        );

        return back()->with('success', "Case status transitioned to {$validated['to_status']}.");
    }

    /**
     * Add an internal note (strictly staff-only).
     */
    public function addNote(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $this->workflow->addInternalNote(
            application: $application,
            actor: $user,
            note: $validated['note']
        );

        return back()->with('success', 'Internal note recorded.');
    }

    /**
     * Reassign case to another staff member or mentor.
     */
    public function assign(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'assignee_id' => ['required', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $assignee = User::findOrFail($validated['assignee_id']);

        // Ensure assigned mentor/volunteer is marked as approved helper so mobile app shows all assigned tasks
        if ($assignee->hasRole(['mentor', 'volunteer']) && $assignee->helper_status !== User::HELPER_APPROVED) {
            $assignee->helper_status = User::HELPER_APPROVED;
            $assignee->save();
        }

        DB::transaction(function () use ($application, $assignee, $user, $validated) {
            $prevAssigneeId = $application->current_assignee_id;
            $prevStatus = $application->status;
            $application->current_assignee_id = $assignee->id;

            $stagesBeforeAssigned = [
                Application::STATUS_RECEIVED,
                Application::STATUS_VERIFICATION,
                Application::STATUS_CATEGORISED,
            ];

            if (in_array($application->status, $stagesBeforeAssigned, true)) {
                // Automatically complete previous statuses so citizen tracker and timeline mark them done
                if ($application->status === Application::STATUS_RECEIVED) {
                    ApplicationTimelineEvent::firstOrCreate(
                        [
                            'application_id' => $application->id,
                            'event_type' => 'status_changed_verification',
                        ],
                        [
                            'from_status' => Application::STATUS_RECEIVED,
                            'to_status' => Application::STATUS_VERIFICATION,
                            'title_key' => 'app.timeline.status_verification',
                            'body' => 'Application verified by administration prior to mentor assignment.',
                            'actor_id' => $user->id,
                            'actor_role' => $user->getRoleNames()->first() ?? 'admin',
                            'visibility' => 'public',
                            'created_at' => now(),
                        ]
                    );

                    ApplicationTimelineEvent::firstOrCreate(
                        [
                            'application_id' => $application->id,
                            'event_type' => 'status_changed_categorised',
                        ],
                        [
                            'from_status' => Application::STATUS_VERIFICATION,
                            'to_status' => Application::STATUS_CATEGORISED,
                            'title_key' => 'app.timeline.status_categorised',
                            'body' => 'Application desk-categorised and routed for mentor allocation.',
                            'actor_id' => $user->id,
                            'actor_role' => $user->getRoleNames()->first() ?? 'admin',
                            'visibility' => 'public',
                            'created_at' => now(),
                        ]
                    );
                } elseif ($application->status === Application::STATUS_VERIFICATION) {
                    ApplicationTimelineEvent::firstOrCreate(
                        [
                            'application_id' => $application->id,
                            'event_type' => 'status_changed_categorised',
                        ],
                        [
                            'from_status' => Application::STATUS_VERIFICATION,
                            'to_status' => Application::STATUS_CATEGORISED,
                            'title_key' => 'app.timeline.status_categorised',
                            'body' => 'Application desk-categorised and routed for mentor allocation.',
                            'actor_id' => $user->id,
                            'actor_role' => $user->getRoleNames()->first() ?? 'admin',
                            'visibility' => 'public',
                            'created_at' => now(),
                        ]
                    );
                }

                $application->status = Application::STATUS_ASSIGNED;
                $application->sla_due_at = app(SLAEngineService::class)
                    ->calculateDueDate($application->urgency, $application->priority);
            }

            $application->save();

            // Record assignment history
            ApplicationAssignment::create([
                'application_id' => $application->id,
                'assignee_id' => $assignee->id,
                'assignee_type' => $assignee->getRoleNames()->first() ?? 'staff',
                'assigned_by' => $user->id,
                'reason' => $validated['reason'] ?? null,
                'created_at' => now(),
            ]);

            // If status changed to assigned, record status transition event
            if ($prevStatus !== Application::STATUS_ASSIGNED && $application->status === Application::STATUS_ASSIGNED) {
                ApplicationTimelineEvent::create([
                    'application_id' => $application->id,
                    'event_type' => 'status_changed_assigned',
                    'from_status' => $prevStatus,
                    'to_status' => Application::STATUS_ASSIGNED,
                    'title_key' => 'app.timeline.status_assigned',
                    'body' => "Case status advanced to assigned upon allocating dedicated mentor {$assignee->name}.",
                    'actor_id' => $user->id,
                    'actor_role' => $user->getRoleNames()->first() ?? 'admin',
                    'visibility' => 'public',
                    'created_at' => now(),
                ]);
            }

            // Timeline event for assignment / reassignment
            ApplicationTimelineEvent::create([
                'application_id' => $application->id,
                'event_type' => 'case_reassigned',
                'from_status' => $application->status,
                'to_status' => $application->status,
                'title_key' => 'app.timeline.reassigned',
                'body' => "Reassigned to {$assignee->name}. Reason: ".($validated['reason'] ?? 'Workload rebalance'),
                'actor_id' => $user->id,
                'actor_role' => $user->getRoleNames()->first() ?? 'admin',
                'visibility' => 'public',
                'created_at' => now(),
            ]);

            AuditLog::record(
                action: 'case.assign',
                subject: $application,
                before: [
                    'assignee_id' => $prevAssigneeId,
                    'status' => $prevStatus,
                ],
                after: [
                    'assignee_id' => $assignee->id,
                    'status' => $application->status,
                ],
                actorId: $user->id
            );
        });

        $this->notifier->dispatch(
            recipient: $assignee,
            eventKey: 'case_assigned',
            variables: [
                'case_no' => $application->case_no,
                'title' => $application->title,
                'status' => $application->status,
            ],
            extraData: [
                'application_id' => $application->id,
                'case_no' => $application->case_no,
                'deep_link' => "thh://applications/{$application->id}",
            ]
        );

        return back()->with('success', "Case assigned to {$assignee->name}.");
    }

    /**
     * Assigned helper confirms they will proceed.
     */
    public function accept(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)
            ->where('current_assignee_id', $user->id)
            ->findOrFail($id);

        ApplicationAssignment::where('application_id', $application->id)
            ->where('assignee_id', $user->id)
            ->whereNull('accepted_at')
            ->update(['accepted_at' => now()]);

        if ($application->status === Application::STATUS_ASSIGNED) {
            $this->workflow->transition(
                application: $application,
                toStatus: Application::STATUS_ASSISTANCE,
                actor: $user,
                note: 'Assignment accepted. Helper is proceeding.',
                visibility: 'public'
            );
        }

        return back()->with('success', 'You accepted this case.');
    }

    /**
     * Schedule a case follow-up.
     */
    public function addFollowUp(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $application = ScopeHelper::applyApplicationScope(Application::query(), $user)->findOrFail($id);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        FollowUp::create([
            'application_id' => $application->id,
            'scheduled_for' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'assigned_to' => $user->id,
        ]);

        return back()->with('success', 'Follow-up reminder scheduled.');
    }
}
