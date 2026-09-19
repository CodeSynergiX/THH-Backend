<?php

namespace App\Http\Controllers;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    /**
     * Render the public citizen portal homepage with live database stats.
     */
    public function index(Request $request): Response
    {
        $resolvedCount = Application::where('status', Application::STATUS_RESOLVED)->count();
        $citizensCount = User::whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->count();
        $villagesCount = Village::where('is_active', true)->count();

        $categories = Category::where('is_active', true)
            ->withCount('applications')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('welcome', [
            'stats' => [
                'resolved_cases' => $resolvedCount,
                'citizens_helped' => $citizensCount,
                'villages_covered' => $villagesCount,
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Quick case status lookup by Case Number (e.g. THH-2026-00001).
     */
    public function track(Request $request, string $caseNo): JsonResponse
    {
        $case = Application::where('case_no', strtoupper(trim($caseNo)))
            ->with(['category', 'village.taluka.district'])
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found with the provided case number.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case_no' => $case->case_no,
                'status' => $case->status,
                'title' => $case->title,
                'category' => $case->category?->slug,
                'district' => $case->village?->taluka?->district?->name_en,
                'created_at' => $case->created_at?->toIso8601String(),
                'resolved_at' => $case->resolved_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Show public community module details with active listings.
     */
    public function showModule(Request $request, string $module): Response
    {
        $module = strtolower($module);

        $items = match ($module) {
            'schemes' => Scheme::where('is_published', true)->take(20)->get(),
            'scholarships' => Scholarship::where('is_published', true)->take(20)->get(),
            'jobs' => JobPosting::where('is_active', true)->take(20)->get(),
            'health' => HealthCamp::where('is_active', true)->take(20)->get(),
            'blood' => BloodRequest::whereIn('status', ['urgent', 'pending', 'open'])->take(20)->get(),
            'mock_tests' => MockTest::where('is_published', true)->take(20)->get(),
            'sakhi' => SakhiCircle::where('is_active', true)->take(20)->get(),
            'village_reports' => VillageReport::with(['village.taluka.district', 'user'])->latest()->take(20)->get(),
            default => Scheme::where('is_published', true)->take(10)->get(),
        };

        $districts = District::with(['talukas.villages'])->where('is_active', true)->get();
        $categories = Category::with('subCategories')->where('is_active', true)->get();

        return Inertia::render('public/module', [
            'module' => $module,
            'items' => $items,
            'districts' => $districts,
            'categories' => $categories,
        ]);
    }

    /**
     * Public Citizen Assistance Application submission.
     */
    public function applyHelp(Request $request, string $module): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'beneficiary_name' => 'required|string|max:150',
            'beneficiary_phone' => 'required|string|min:10|max:15',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'district_id' => 'required|exists:districts,id',
            'taluka_id' => 'nullable|exists:talukas,id',
            'village_id' => 'nullable|exists:villages,id',
            'urgency' => 'nullable|string|in:normal,urgent,emergency',
        ]);

        $category = Category::where('slug', $module)->first()
            ?? Category::where('is_active', true)->first();
        $categoryId = $category ? $category->id : 1;

        $year = date('Y');
        $random = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        $caseNo = "THH-{$year}-{$random}";

        $citizenUser = auth()->user()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->first()
            ?? User::first();
        $userId = $citizenUser ? $citizenUser->id : 1;

        $application = Application::create([
            'case_no' => $caseNo,
            'user_id' => $userId,
            'category_id' => $categoryId,
            'village_id' => $validated['village_id'] ?? null,
            'title' => $validated['title'],
            'description' => "Applicant: {$validated['beneficiary_name']} (Phone: {$validated['beneficiary_phone']})\n\n{$validated['description']}",
            'urgency' => $validated['urgency'] ?? 'normal',
            'priority' => 'medium',
            'status' => Application::STATUS_RECEIVED,
            'sla_due_at' => now()->addDays(7),
        ]);

        // Record timeline event
        ApplicationTimelineEvent::create([
            'application_id' => $application->id,
            'event_type' => 'status_changed_received',
            'from_status' => null,
            'to_status' => Application::STATUS_RECEIVED,
            'title_key' => 'app.timeline.status_received',
            'body' => 'Application submitted through public community portal.',
            'actor_id' => $userId,
            'actor_role' => 'citizen',
            'visibility' => 'public',
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Help request submitted successfully!',
                'data' => [
                    'case_no' => $application->case_no,
                    'status' => $application->status,
                ],
            ]);
        }

        return back()->with('success_case', $application->case_no);
    }
}
