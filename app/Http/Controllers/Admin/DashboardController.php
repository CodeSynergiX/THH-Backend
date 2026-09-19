<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Services\SLAEngineService;
use App\Domains\Content\Models\Category;
use App\Domains\Users\Models\District;
use App\Domains\Users\Scopes\ScopeHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected SLAEngineService $slaEngine
    ) {}

    /**
     * Render the role-aware NGO administrative dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Base scoped query
        $baseQuery = ScopeHelper::applyApplicationScope(Application::query(), $user);

        // 2. Metric Counters
        $totalCases = (clone $baseQuery)->count();
        $inVerification = (clone $baseQuery)->where('status', Application::STATUS_VERIFICATION)->count();
        $inAssistance = (clone $baseQuery)->whereIn('status', [Application::STATUS_ASSIGNED, Application::STATUS_ASSISTANCE])->count();
        $resolvedCases = (clone $baseQuery)->where('status', Application::STATUS_RESOLVED)->count();
        $slaBreached = (clone $baseQuery)->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<', now())
            ->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->count();
        $avgRating = (clone $baseQuery)->whereNotNull('rating')->avg('rating');

        // 3. Urgent / SLA Attention Queue
        $urgentCases = (clone $baseQuery)->whereNotIn('status', [Application::STATUS_RESOLVED, Application::STATUS_REJECTED])
            ->with(['category', 'village.taluka.district', 'currentAssignee'])
            ->orderByRaw('CASE WHEN sla_due_at IS NOT NULL AND sla_due_at < CURRENT_TIMESTAMP THEN 0 ELSE 1 END')
            ->orderBy('sla_due_at', 'asc')
            ->limit(5)
            ->get();

        // 4. Recent Timeline Events
        $recentTimeline = ApplicationTimelineEvent::with(['application:id,case_no,title', 'actor:id,name'])
            ->whereHas('application', function ($q) use ($user) {
                ScopeHelper::applyApplicationScope($q, $user);
            })
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        // 5. Category Breakdown (Real DB values)
        $categoryBreakdown = Category::where('is_active', true)
            ->withCount(['applications' => function ($q) use ($user) {
                ScopeHelper::applyApplicationScope($q, $user);
            }])
            ->get(['id', 'slug', 'icon']);

        // 6. District Distribution
        $districtBreakdown = District::where('is_active', true)
            ->withCount(['villages'])
            ->limit(6)
            ->get(['id', 'name_en', 'name_gu']);

        return Inertia::render('admin/dashboard/index', [
            'metrics' => [
                'total_cases' => $totalCases,
                'in_verification' => $inVerification,
                'in_assistance' => $inAssistance,
                'resolved' => $resolvedCases,
                'sla_breached' => $slaBreached,
                'avg_rating' => $avgRating ? round((float) $avgRating, 1) : null,
            ],
            'urgent_cases' => $urgentCases,
            'recent_timeline' => $recentTimeline,
            'category_breakdown' => $categoryBreakdown,
            'district_breakdown' => $districtBreakdown,
        ]);
    }
}
