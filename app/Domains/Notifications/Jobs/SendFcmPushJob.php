<?php

namespace App\Domains\Notifications\Jobs;

use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Users\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

/**
 * Sends an FCM push notification to all device tokens belonging to a user.
 * Gracefully stubs (logs only) when Firebase credentials are not configured.
 */
class SendFcmPushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        private readonly int $userId,
        private readonly string $title,
        private readonly string $body,
        /** @var array<string, mixed> */
        private readonly array $data = [],
        private readonly ?int $notificationLogId = null,
    ) {}

    public function handle(): void
    {
        $tokens = DeviceToken::where('user_id', $this->userId)->pluck('token')->toArray();

        if (empty($tokens)) {
            return;
        }

        try {
            // If Firebase Messaging is bound in the container, use it
            if (app()->bound(Messaging::class)) {
                $messaging = app(Messaging::class);

                foreach ($tokens as $token) {
                    $message = CloudMessage::new()
                        ->withToken($token)
                        ->withNotification(Notification::create($this->title, $this->body))
                        ->withData($this->data);

                    $messaging->send($message);
                }

                // Update notification log status to delivered
                if ($this->notificationLogId) {
                    NotificationLog::where('id', $this->notificationLogId)->update(['status' => 'delivered']);
                }

                Log::info("SendFcmPushJob: Sent to {$this->userId} via ".count($tokens).' device(s).');
            } else {
                // Firebase not configured — log as stubbed so dev can track
                Log::info("SendFcmPushJob: Firebase not bound (FIREBASE_CREDENTIALS not set) — stubbed for user {$this->userId}. Title: {$this->title}");

                if ($this->notificationLogId) {
                    NotificationLog::where('id', $this->notificationLogId)
                        ->update(['status' => 'sent', 'error' => 'FCM stubbed: Firebase credentials not configured']);
                }
            }
        } catch (\Throwable $e) {
            Log::error("SendFcmPushJob failed for user {$this->userId}: ".$e->getMessage());

            if ($this->notificationLogId) {
                NotificationLog::where('id', $this->notificationLogId)
                    ->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }

            $this->fail($e);
        }
    }
}
