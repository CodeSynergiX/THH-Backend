<?php

namespace App\Domains\Cases\Services;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Models\WorkflowTransition;
use App\Domains\Notifications\Services\NotificationDispatcherService;
use App\Domains\Settings\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApplicationWorkflowService
{
    public function __construct(
        protected SLAEngineService $slaEngine,
        protected NotificationDispatcherService $notifier
    ) {}

    /**
     * Transition an application to a new status.
     *
     * @throws InvalidArgumentException
     */
    public function transition(
        Application $application,
        string $toStatus,
        User $actor,
        ?string $note = null,
        string $visibility = 'public',
        ?array $meta = null
    ): Application {
        $fromStatus = $application->status;

        // If transitioning to the exact same status, record update note without failing
        if ($fromStatus === $toStatus) {
            if (! empty($note)) {
                $actorRole = $actor->getRoleNames()->first() ?? 'mentor';
                ApplicationTimelineEvent::create([
                    'application_id' => $application->id,
                    'event_type' => 'status_note_added',
                    'from_status' => $toStatus,
                    'to_status' => $toStatus,
                    'title_key' => "app.timeline.status_{$toStatus}",
                    'body' => $note,
                    'actor_id' => $actor->id,
                    'actor_role' => $actorRole,
                    'visibility' => $visibility,
                    'meta' => $meta,
                    'created_at' => now(),
                ]);
            }

            return $application;
        }

        // 1. Verify if the transition is allowed
        $transition = WorkflowTransition::where('from_status', $fromStatus)
            ->where('to_status', $toStatus)
            ->first();

        // If defined in workflow_transitions table, check role
        if ($transition) {
            $userRoles = $actor->getRoleNames()->all();
            $allowed = false;
            foreach ($userRoles as $role) {
                if ($transition->allowsRole($role) || $role === 'super_admin' || $role === 'admin') {
                    $allowed = true;
                    break;
                }
            }

            // Also permit if actor is the designated assignee for helper workflow transitions
            if (! $allowed && $actor->id === $application->current_assignee_id && in_array($toStatus, [
                Application::STATUS_ASSISTANCE,
                Application::STATUS_FOLLOW_UP,
                Application::STATUS_AWAITING_CONFIRMATION,
                Application::STATUS_NEED_MORE_INFO,
                Application::STATUS_VERIFICATION,
                Application::STATUS_ON_HOLD,
                Application::STATUS_RESOLVED,
                Application::STATUS_REJECTED,
            ])) {
                $allowed = true;
            }

            if (! $allowed) {
                throw new InvalidArgumentException("Role not permitted to transition application from {$fromStatus} to {$toStatus}.");
            }

            if ($transition->requires_note && empty($note)) {
                throw new InvalidArgumentException("A note is required for transition from {$fromStatus} to {$toStatus}.");
            }
        } elseif ($actor->hasRole(['super_admin', 'admin', 'staff'])) {
            $allowed = true;
        } elseif ($actor->id === $application->current_assignee_id && in_array($toStatus, [
            Application::STATUS_ASSISTANCE,
            Application::STATUS_FOLLOW_UP,
            Application::STATUS_AWAITING_CONFIRMATION,
            Application::STATUS_NEED_MORE_INFO,
            Application::STATUS_VERIFICATION,
            Application::STATUS_ON_HOLD,
            Application::STATUS_RESOLVED,
            Application::STATUS_REJECTED,
        ])) {
            $allowed = true;
        } else {
            throw new InvalidArgumentException("Invalid status transition from {$fromStatus} to {$toStatus}.");
        }

        // 2. Perform transition inside DB transaction
        return DB::transaction(function () use ($application, $fromStatus, $toStatus, $actor, $note, $visibility, $meta) {
            $oldAttributes = $application->only(['status', 'sla_due_at', 'resolved_at']);

            $application->status = $toStatus;

            if ($toStatus === Application::STATUS_RESOLVED) {
                $application->resolved_at = now();
            }

            // Recalculate SLA if entering assistance or assigned
            if (in_array($toStatus, [Application::STATUS_ASSIGNED, Application::STATUS_ASSISTANCE, Application::STATUS_VERIFICATION])) {
                $application->sla_due_at = $this->slaEngine->calculateDueDate($application->urgency, $application->priority);
            }

            $application->save();

            // 3. Write immutable timeline event
            $actorRole = $actor->getRoleNames()->first() ?? 'citizen';

            if ($fromStatus === Application::STATUS_ASSIGNED && $toStatus === Application::STATUS_AWAITING_CONFIRMATION) {
                ApplicationTimelineEvent::create([
                    'application_id' => $application->id,
                    'event_type' => 'status_changed_'.Application::STATUS_ASSISTANCE,
                    'from_status' => Application::STATUS_ASSIGNED,
                    'to_status' => Application::STATUS_ASSISTANCE,
                    'title_key' => 'app.timeline.status_'.Application::STATUS_ASSISTANCE,
                    'body' => 'સ્થળ તપાસ અને સહાય પ્રક્રિયા પૂર્ણ. (Field visit and assistance completed)',
                    'actor_id' => $actor->id,
                    'actor_role' => $actorRole,
                    'visibility' => 'public',
                    'notification_status' => ['email' => 'skipped', 'push' => 'skipped'],
                    'created_at' => now()->subSecond(),
                ]);
            }

            $event = ApplicationTimelineEvent::create([
                'application_id' => $application->id,
                'event_type' => "status_changed_{$toStatus}",
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'title_key' => "app.timeline.status_{$toStatus}",
                'body' => $note,
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'visibility' => $visibility,
                'meta' => $meta,
                'notification_status' => ['email' => 'pending', 'push' => 'pending'],
                'created_at' => now(),
            ]);

            // 4. Record audit log
            AuditLog::record(
                action: "case.transition.{$toStatus}",
                subject: $application,
                before: $oldAttributes,
                after: $application->only(['status', 'sla_due_at', 'resolved_at']),
                actorId: $actor->id
            );

            $notificationStatus = ['email' => 'skipped', 'push' => 'skipped'];
            if ($application->user) {
                $log = $this->notifier->dispatch(
                    recipient: $application->user,
                    eventKey: "case_status_{$toStatus}",
                    variables: [
                        'case_no' => $application->case_no,
                        'status' => $toStatus,
                        'title' => $application->title,
                        'note' => $note ?? '',
                    ],
                    extraData: [
                        'application_id' => $application->id,
                        'case_no' => $application->case_no,
                        'deep_link' => "thh://applications/{$application->id}",
                    ]
                );
                $notificationStatus['email'] = $application->user->email ? 'sent' : 'skipped';
                $notificationStatus['push'] = $log ? 'sent' : 'skipped';
                $notificationStatus['at'] = now()->toIso8601String();
            }
            $event->update(['notification_status' => $notificationStatus]);

            return $application;
        });
    }

    /**
     * Add an internal note to the case (visibility = internal).
     */
    public function addInternalNote(Application $application, User $actor, string $note): ApplicationTimelineEvent
    {
        $actorRole = $actor->getRoleNames()->first() ?? 'staff';

        return DB::transaction(function () use ($application, $actor, $note, $actorRole) {
            $event = ApplicationTimelineEvent::create([
                'application_id' => $application->id,
                'event_type' => 'internal_note_added',
                'from_status' => $application->status,
                'to_status' => $application->status,
                'title_key' => 'app.timeline.internal_note',
                'body' => $note,
                'actor_id' => $actor->id,
                'actor_role' => $actorRole,
                'visibility' => 'internal', // STRICTLY INTERNAL!
                'created_at' => now(),
            ]);

            AuditLog::record(
                action: 'case.internal_note',
                subject: $application,
                before: null,
                after: ['note_id' => $event->id],
                actorId: $actor->id
            );

            return $event;
        });
    }
}
