<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Throwable;

class NotificationDispatcherService
{
    /**
     * Dispatch notification to user using template key, interpolating variables in user's locale.
     */
    public function dispatch(
        User $recipient,
        string $eventKey,
        array $variables = [],
        ?array $extraData = null
    ): ?NotificationLog {
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

        // Send FCM push notification to recipient's registered device tokens
        $this->sendPush($recipient, $title, $body, $extraData, $log);

        return $log;
    }

    protected function sendPush(User $recipient, string $title, string $body, ?array $data, NotificationLog $log): void
    {
        $tokens = $recipient->deviceTokens()->pluck('token')->all();
        if (empty($tokens)) {
            return;
        }

        try {
            if (app()->bound(Messaging::class)) {
                $messaging = app(Messaging::class);
                foreach ($tokens as $token) {
                    $message = CloudMessage::new()
                        ->withToken($token)
                        ->withNotification(Notification::create($title, $body))
                        ->withData($data ?? []);

                    $messaging->send($message);
                }
                $log->update(['status' => 'delivered']);
            }
        } catch (Throwable $e) {
            Log::warning("FCM delivery notice for user {$recipient->id}: ".$e->getMessage());
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}
