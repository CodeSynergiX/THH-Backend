<?php

namespace App\Http\Controllers;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Services\ApplicationWorkflowService;
use App\Domains\Notifications\Models\NotificationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(
        protected ApplicationWorkflowService $workflow
    ) {}

    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $base = Application::query()->where('user_id', $user->id);

        $statusCounts = [
            'all' => (clone $base)->count(),
            'open' => (clone $base)->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])->count(),
            Application::STATUS_AWAITING_CONFIRMATION => (clone $base)->where('status', Application::STATUS_AWAITING_CONFIRMATION)->count(),
            Application::STATUS_RESOLVED => (clone $base)->where('status', Application::STATUS_RESOLVED)->count(),
        ];

        $applications = (clone $base)
            ->with(['category', 'currentAssignee:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        $nextAppointment = FollowUp::query()
            ->with(['application:id,case_no,title', 'assignee:id,name'])
            ->whereHas('application', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'pending')
            ->where('scheduled_for', '>=', now())
            ->orderBy('scheduled_for')
            ->first();

        $unread = NotificationLog::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return Inertia::render('account/dashboard', [
            'status_counts' => $statusCounts,
            'applications' => $applications,
            'next_appointment' => $nextAppointment,
            'unread_notifications' => $unread,
        ]);
    }

    public function applications(Request $request): Response
    {
        $applications = Application::query()
            ->where('user_id', $request->user()->id)
            ->with(['category', 'currentAssignee:id,name'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('account/applications', [
            'applications' => $applications,
        ]);
    }

    public function show(Request $request, int $id): Response
    {
        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'category',
                'subCategory',
                'village.taluka.district',
                'currentAssignee:id,name',
                'publicTimelineEvents.actor:id,name',
                'followUps.assignee:id,name',
                'documents',
                'user:id,name,phone,email',
            ])
            ->findOrFail($id);

        return Inertia::render('account/show', [
            'application' => $application,
        ]);
    }

    public function confirm(Request $request, int $id): RedirectResponse
    {
        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $this->workflow->transition(
            application: $application,
            toStatus: Application::STATUS_RESOLVED,
            actor: $request->user(),
            note: $request->input('note', 'Citizen confirmed the case is resolved.'),
            visibility: 'public'
        );

        return back()->with('success', 'Thank you. The case is now closed.');
    }

    public function reopen(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $application = Application::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        $this->workflow->transition(
            application: $application,
            toStatus: Application::STATUS_REOPENED,
            actor: $request->user(),
            note: $validated['reason'],
            visibility: 'public'
        );

        return back()->with('success', 'The desk will look at this case again.');
    }

    public function appointments(Request $request): Response
    {
        $appointments = FollowUp::query()
            ->with(['application:id,case_no,title,status', 'assignee:id,name'])
            ->whereHas('application', fn ($q) => $q->where('user_id', $request->user()->id))
            ->orderByDesc('scheduled_for')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('account/appointments', [
            'appointments' => $appointments,
        ]);
    }

    public function notifications(Request $request): Response
    {
        $notifications = NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('account/notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationRead(Request $request, int $id): RedirectResponse
    {
        NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->update(['read_at' => now()]);

        return back();
    }
}
