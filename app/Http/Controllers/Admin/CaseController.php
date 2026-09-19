<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationAssignment;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Models\WorkflowTransition;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Content\Models\Category;
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
        protected ApplicationWorkflowService $workflow
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
            $q->whereIn('name', ['staff', 'mentor']);
        })
            ->where('is_active', true)
            ->withCount(['assignedApplications' => function ($q) {
                $q->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED]);
            }])
            ->get(['id', 'name', 'email']);

        // Eligible transitions based on current status and user's role
        $userRoles = $user->getRoleNames()->all();
        $allowedTransitions = WorkflowTransition::where('from_status', $application->status)
            ->get()
            ->filter(function ($t) use ($userRoles) {
                foreach ($userRoles as $role) {
                    if ($t->allowsRole($role) || in_array($role, ['super_admin', 'admin'])) {
                        return true;
                    }
                }

                return false;
            })
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

        DB::transaction(function () use ($application, $assignee, $user, $validated) {
            $prevAssigneeId = $application->current_assignee_id;
            $application->current_assignee_id = $assignee->id;

            if ($application->status === Application::STATUS_CATEGORISED || $application->status === Application::STATUS_VERIFICATION) {
                $application->status = Application::STATUS_ASSIGNED;
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

            // Timeline event
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
                before: ['assignee_id' => $prevAssigneeId],
                after: ['assignee_id' => $assignee->id],
                actorId: $user->id
            );
        });

        return back()->with('success', "Case assigned to {$assignee->name}.");
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
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'conducted_by' => $user->id,
        ]);

        return back()->with('success', 'Follow-up reminder scheduled.');
    }
}
