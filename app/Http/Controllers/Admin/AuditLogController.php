<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * System Audit Log & Timeline inspection.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search', '');
        $action = $request->query('action', 'all');

        $query = AuditLog::with('actor:id,name,email');

        if ($action !== 'all') {
            $query->where('action', 'like', "{$action}%");
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('actor', function ($aq) use ($search) {
                        $aq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $logs = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $actions = AuditLog::select('action')->distinct()->pluck('action')->all();

        return Inertia::render('admin/audit/index', [
            'logs' => $logs,
            'search' => $search,
            'selectedAction' => $action,
            'actions' => $actions,
        ]);
    }
}
