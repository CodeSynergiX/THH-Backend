<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Cases\Models\FollowUp;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = FollowUp::query()->with([
            'application:id,case_no,title,status,user_id,current_assignee_id',
            'application.user:id,name',
            'assignee:id,name',
        ]);

        if ($user->hasRole(['mentor', 'volunteer'])) {
            $query->where('assigned_to', $user->id);
        } else {
            $query->whereHas('application', fn ($q) => ScopeHelper::applyApplicationScope($q, $user));
        }

        $appointments = $query->orderBy('scheduled_for')->paginate(20)->withQueryString();

        return Inertia::render('admin/appointments/index', [
            'appointments' => $appointments,
        ]);
    }
}
