<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Notifications\Models\NotificationPreference;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /api/v1/notifications — paginated notification log. */
    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = NotificationLog::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved.',
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /** POST /api/v1/notifications/{id}/read */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = NotificationLog::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /** POST /api/v1/notifications/read-all */
    public function markAllAsRead(Request $request): JsonResponse
    {
        NotificationLog::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /** POST /api/v1/notifications/receipt — FCM delivery/open receipt from device. */
    public function receipt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notification_id' => ['required', 'exists:notifications_log,id'],
            'status' => ['required', 'in:delivered,opened'],
        ]);

        NotificationLog::where('id', $validated['notification_id'])
            ->where('user_id', $request->user()->id)
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery receipt recorded.',
        ]);
    }

    /** GET /api/v1/notification-preferences */
    public function preferences(Request $request): JsonResponse
    {
        $prefs = NotificationPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            [] // all defaults are true from migration
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences retrieved.',
            'data' => $prefs,
        ]);
    }

    /** PUT /api/v1/notification-preferences */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_enabled' => ['sometimes', 'boolean'],
            'push_enabled' => ['sometimes', 'boolean'],
            'email_case_status_change' => ['sometimes', 'boolean'],
            'email_assignment' => ['sometimes', 'boolean'],
            'email_follow_up_due' => ['sometimes', 'boolean'],
            'email_sla_breach' => ['sometimes', 'boolean'],
            'email_case_resolved' => ['sometimes', 'boolean'],
            'push_case_status_change' => ['sometimes', 'boolean'],
            'push_assignment' => ['sometimes', 'boolean'],
            'push_follow_up_due' => ['sometimes', 'boolean'],
            'push_sla_breach' => ['sometimes', 'boolean'],
            'push_case_resolved' => ['sometimes', 'boolean'],
            'quiet_hours_start' => ['sometimes', 'nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['sometimes', 'nullable', 'date_format:H:i'],
        ]);

        $prefs = NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated.',
            'data' => $prefs,
        ]);
    }
}
