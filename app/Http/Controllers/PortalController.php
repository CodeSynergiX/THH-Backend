<?php

namespace App\Http\Controllers;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Services\ApplicationSubmissionService;
use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\ContentItem;
use App\Domains\Content\Models\ContentModule;
use App\Domains\Settings\Models\StaticPage;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Village;
use App\Domains\Users\Services\OtpService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    public function __construct(
        protected ApplicationSubmissionService $submissions,
        protected OtpService $otp
    ) {}

    /**
     * Render the public citizen portal homepage with live database stats.
     */
    public function index(Request $request): Response
    {
        $resolvedCount = Application::where('status', Application::STATUS_RESOLVED)->count();
        $citizensCount = User::whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->count();
        $villagesCount = Village::where('is_active', true)->count();

        $modules = ContentModule::query()
            ->public()
            ->withCount('publishedItems')
            ->orderBy('sort_order')
            ->get();

        $homeBlocks = ContentModule::query()
            ->where('slug', 'home')
            ->with(['publishedItems'])
            ->first()?->publishedItems
            ?->map(fn (ContentItem $item) => [
                'slug' => $item->slug,
                'title_en' => $item->title_en,
                'title_gu' => $item->title_gu,
                'excerpt_en' => $item->excerpt_en,
                'excerpt_gu' => $item->excerpt_gu,
                'body_en' => $item->body_en,
                'body_gu' => $item->body_gu,
            ])
            ?->values() ?? collect();

        return Inertia::render('welcome', [
            'stats' => [
                'resolved_cases' => $resolvedCount,
                'citizens_helped' => $citizensCount,
                'villages_covered' => $villagesCount,
            ],
            'modules' => $modules->where('slug', '!=', 'home')->values(),
            'homeBlocks' => $homeBlocks,
            'authUser' => $request->user(),
        ]);
    }

    public function requestTrackOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(['case_no' => ['required', 'string']]);
        $case = Application::query()->with('user')->where('case_no', strtoupper(trim($validated['case_no'])))->first();

        if (! $case?->user?->email) {
            return response()->json([
                'success' => false,
                'message' => 'No email is registered for this case.',
            ], 404);
        }

        $code = $this->otp->issue(null, $case->user->email, 'track');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to the application email.',
            'data' => [
                'email_hint' => substr($case->user->email, 0, 2).'***',
                'debug_code' => null,
            ],
        ]);
    }

    public function track(Request $request, string $caseNo): JsonResponse
    {
        $case = Application::query()
            ->with(['category', 'village.taluka.district', 'user', 'timelineEvents' => fn ($q) => $q->where('visibility', 'public')->with('actor:id,name')])
            ->where('case_no', strtoupper(trim($caseNo)))
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found with the provided case number.',
            ], 404);
        }

        $user = $request->user();
        $allowed = $user && (
            $user->id === $case->user_id
            || $user->hasRole(['super_admin', 'admin', 'staff', 'collector'])
        );

        if (! $allowed && $request->filled('otp') && $case->user?->email) {
            $allowed = $this->otp->verify(null, $case->user->email, (string) $request->input('otp'), 'track');
        }

        if (! $allowed) {
            return response()->json([
                'success' => false,
                'requires_auth' => true,
                'message' => 'Login or verify OTP sent to the application email.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case_no' => $case->case_no,
                'status' => $case->status,
                'title' => $case->title,
                'description' => $case->description,
                'category' => $case->category?->slug,
                'category_name' => $case->category?->name_en,
                'district' => $case->village?->taluka?->district?->name_en,
                'taluka' => $case->village?->taluka?->name_en,
                'village' => $case->village?->name_en,
                'lat' => $case->lat !== null ? (float) $case->lat : null,
                'lng' => $case->lng !== null ? (float) $case->lng : null,
                'sla_due_at' => $case->sla_due_at?->toIso8601String(),
                'created_at' => $case->created_at?->toIso8601String(),
                'resolved_at' => $case->resolved_at?->toIso8601String(),
                'applicant_name' => $case->user?->name,
                'timeline' => $case->timelineEvents->map(fn ($event) => [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'body' => $event->body,
                    'actor' => $event->actor?->name,
                    'actor_role' => $event->actor_role,
                    'created_at' => $event->created_at?->toIso8601String(),
                    'notification_status' => $event->notification_status,
                ]),
            ],
        ]);
    }

    public function showModule(Request $request, string $module): Response
    {
        $record = ContentModule::query()->public()->where('slug', strtolower($module))->firstOrFail();
        $items = $record->publishedItems()->paginate(20)->withQueryString();
        $districts = District::with(['talukas.villages'])->where('is_active', true)->get();
        $categories = Category::with('subCategories')->where('is_active', true)->get();

        return Inertia::render('public/module', [
            'module' => $record,
            'items' => $items,
            'districts' => $districts,
            'categories' => $categories,
        ]);
    }

    public function showItem(Request $request, string $module, string $item): Response
    {
        $record = ContentModule::query()->public()->where('slug', strtolower($module))->firstOrFail();
        $content = $record->publishedItems()->where('slug', $item)->firstOrFail();
        $districts = District::with(['talukas.villages'])->where('is_active', true)->get();

        return Inertia::render('public/item', [
            'module' => $record,
            'item' => $content,
            'districts' => $districts,
        ]);
    }

    public function showStaticPage(string $slug): Response
    {
        $page = StaticPage::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return Inertia::render('public/static-page', [
            'page' => $page,
        ]);
    }

    public function applyHelp(Request $request, string $module): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'beneficiary_name' => 'required|string|max:150',
            'beneficiary_phone' => 'required|string|min:10|max:15',
            'email' => 'required|email|max:255',
            'title' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
            'district_id' => 'nullable|exists:districts,id',
            'taluka_id' => 'nullable|exists:talukas,id',
            'village_id' => 'nullable|exists:villages,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'urgency' => 'nullable|string|in:low,medium,urgent,normal,critical,emergency',
        ]);

        $resolved = $this->submissions->resolveCitizen($request->user(), [
            ...$validated,
            'name' => $validated['beneficiary_name'],
            'phone' => $validated['beneficiary_phone'],
        ]);

        $application = $this->submissions->submit(
            $resolved['user'],
            [
                ...$validated,
                'module' => $module,
                'name' => $validated['beneficiary_name'],
                'phone' => $validated['beneficiary_phone'],
            ],
            null,
            $resolved['created'],
            $resolved['password']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Help request submitted successfully!',
                'data' => [
                    'case_no' => $application->case_no,
                    'status' => $application->status,
                    'account_created' => $resolved['created'],
                ],
            ]);
        }

        $message = 'Request registered. Case '.$application->case_no.'. Check your email for updates'
            .($resolved['created'] ? ' and login details.' : '.');

        return back()->with('success', $message);
    }
}
