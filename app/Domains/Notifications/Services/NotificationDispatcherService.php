<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Jobs\SendEmailNotificationJob;
use App\Domains\Notifications\Jobs\SendFcmPushJob;
use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationDispatcherService
{
    /**
     * Dispatch a notification to a recipient via all enabled channels,
     * respecting per-user notification preferences.
     *
     * @param  array<string, mixed>  $variables  Template interpolation vars, e.g. ['case_no' => 'THH-2026-00001']
     * @param  array<string, mixed>|null  $extraData  Additional JSON payload (e.g. deep-link route)
     */
    public function dispatch(
        User $recipient,
        string $eventKey,
        array $variables = [],
        ?array $extraData = null
    ): ?NotificationLog {
        // Resolve template
        $template = NotificationTemplate::where('event_key', $eventKey)
            ->where('is_enabled', true)
            ->first();

        $locale = $recipient->locale ?? 'gu';

        if ($template) {
            $rendered = $template->render($locale, $variables);
            $title = $rendered['title'];
            $body = $rendered['body'];
        } else {
            $title = $variables['title'] ?? 'Tribal Helping Hand Update';
            $body = $variables['body'] ?? 'Your application status has been updated.';
        }

        // Create the in-app notification log entry first
        $log = NotificationLog::create([
            'user_id' => $recipient->id,
            'channel' => 'in_app',
            'type' => $eventKey,
            'title' => $title,
            'body' => $body,
            'data' => $extraData,
            'status' => 'sent',
            'read_at' => null,
        ]);

        // Load or resolve user notification preferences
        /** @var NotificationPreference $prefs */
        $prefs = NotificationPreference::firstOrCreate(
            ['user_id' => $recipient->id],
            [] // all defaults are true
        );

        // Email channel
        if ($prefs->emailAllowed($eventKey) && $recipient->email) {
            SendEmailNotificationJob::dispatch($recipient->id, $eventKey, $title, $body, $extraData ?? []);
        }

        // FCM Push channel
        if ($prefs->pushAllowed($eventKey)) {
            SendFcmPushJob::dispatch($recipient->id, $title, $body, $extraData ?? [], $log->id);
        }

        return $log;
    }

    /**
     * Dispatch a notification to multiple recipients efficiently.
     *
     * @param  array<int, User>  $recipients
     * @param  array<string, mixed>  $variables
     * @param  array<string, mixed>|null  $extraData
     */
    public function dispatchToMany(
        array $recipients,
        string $eventKey,
        array $variables = [],
        ?array $extraData = null
    ): void {
        foreach ($recipients as $recipient) {
            try {
                $this->dispatch($recipient, $eventKey, $variables, $extraData);
            } catch (\Throwable $e) {
                Log::error("NotificationDispatcher failed for user {$recipient->id}: ".$e->getMessage());
            }
        }
    }
}
