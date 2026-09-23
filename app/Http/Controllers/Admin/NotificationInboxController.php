<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Notifications\Models\NotificationLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationInboxController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/notifications/index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, int $id): RedirectResponse
    {
        NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($id)
            ->update(['read_at' => now()]);

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        NotificationLog::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
