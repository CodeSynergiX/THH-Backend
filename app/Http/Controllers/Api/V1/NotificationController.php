<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Notifications\Models\NotificationLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = NotificationLog::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved.',
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = NotificationLog::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

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
}
