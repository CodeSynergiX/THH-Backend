<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\Hospital;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Village;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContentModuleController extends Controller
{
    /** Supported module slugs and their metadata */
    public const MODULE_META = [
        'schemes' => [
            'slug' => 'schemes',
            'title' => 'Government Schemes',
            'title_gu' => 'સરકારી યોજનાઓ',
            'description' => 'Awas, Ayushman, Kisan Sahay, Forest Rights eligibility directory.',
            'singular' => 'Scheme',
        ],
        'scholarships' => [
            'slug' => 'scholarships',
            'title' => 'Scholarships',
            'title_gu' => 'છાત્રવૃત્તિ સહાય',
            'description' => 'Tribal welfare post-matric and higher education scholarships.',
            'singular' => 'Scholarship',
        ],
        'jobs' => [
            'slug' => 'jobs',
            'title' => 'Job Postings',
            'title_gu' => 'રોજગાર તકો',
            'description' => 'Verified local vocational, government, and apprenticeship vacancies.',
            'singular' => 'Job Posting',
        ],
        'health' => [
            'slug' => 'health',
            'title' => 'Health Camps & Hospitals',
            'title_gu' => 'આરોગ્ય શિબિર અને દવાખાના',
            'description' => 'Mobile medical units, sickle cell screening, and hospital directories.',
            'singular' => 'Health Camp',
        ],
        'blood' => [
            'slug' => 'blood',
            'title' => 'Emergency Blood Requests',
            'title_gu' => 'ઇમરજન્સી રક્ત સહાય',
            'description' => 'Urgent blood donor matching across tribal talukas.',
            'singular' => 'Blood Request',
        ],
        'mock_tests' => [
            'slug' => 'mock_tests',
            'title' => 'Mock Tests & Study Material',
            'title_gu' => 'મોક ટેસ્ટ અને અભ્યાસ સાહિત્ય',
            'description' => 'Competitive exam prep (GPSC, Police, Forest Guard) in Gujarati.',
            'singular' => 'Mock Test',
        ],
        'sakhi' => [
            'slug' => 'sakhi',
            'title' => 'Sakhi Circles (SHGs)',
            'title_gu' => 'સખી મંડળ પ્રવૃત્તિ',
            'description' => 'Self-help groups, organic tribal handicrafts and micro-credit.',
            'singular' => 'Sakhi Circle',
        ],
        'village_reports' => [
            'slug' => 'village_reports',
            'title' => 'Village Infrastructure Reports',
            'title_gu' => 'ગામ પ્રશ્નો અને ઉકેલ',
            'description' => 'Citizen ground reports on drinking water, electricity and roads.',
            'singular' => 'Village Report',
        ],
    ];

    /**
     * Content & Community Modules Overview Dashboard.
     */
    public function index(Request $request): Response
    {
        $modules = [
            [
                ...self::MODULE_META['schemes'],
                'count' => Scheme::count(),
                'active_count' => Scheme::where('is_published', true)->count(),
            ],
            [
                ...self::MODULE_META['scholarships'],
                'count' => Scholarship::count(),
                'active_count' => Scholarship::where('is_published', true)->count(),
            ],
            [
                ...self::MODULE_META['jobs'],
                'count' => JobPosting::count(),
                'active_count' => JobPosting::where('is_active', true)->count(),
            ],
            [
                ...self::MODULE_META['health'],
                'count' => HealthCamp::count(),
                'active_count' => HealthCamp::where('is_active', true)->count(),
            ],
            [
                ...self::MODULE_META['blood'],
                'count' => BloodRequest::count(),
                'active_count' => BloodRequest::where('status', 'active')->count(),
            ],
            [
                ...self::MODULE_META['mock_tests'],
                'count' => MockTest::count(),
                'active_count' => MockTest::where('is_active', true)->count(),
            ],
            [
                ...self::MODULE_META['sakhi'],
                'count' => SakhiCircle::count(),
                'active_count' => SakhiCircle::where('is_active', true)->count(),
            ],
            [
                ...self::MODULE_META['village_reports'],
                'count' => VillageReport::count(),
                'active_count' => VillageReport::where('status', 'pending')->count(),
            ],
        ];

        return Inertia::render('admin/content/index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Dedicated Management View for a specific Community Module.
     */
    public function showModule(Request $request, string $module): Response
    {
        abort_if(! isset(self::MODULE_META[$module]), 404, 'Unknown community module.');

        $meta = self::MODULE_META[$module];
        $search = $request->query('search', '');

        $items = match ($module) {
            'schemes' => Scheme::query()
                ->when($search, fn ($q) => $q->where('slug', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'scholarships' => Scholarship::query()
                ->when($search, fn ($q) => $q->where('slug', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'jobs' => JobPosting::query()
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('company', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'health' => HealthCamp::with('district')
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('organizer', 'like', "%{$search}%"))
                ->orderBy('scheduled_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'blood' => BloodRequest::with('hospital')
                ->when($search, fn ($q) => $q->where('patient_name', 'like', "%{$search}%")->orWhere('blood_group', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'mock_tests' => MockTest::withCount('questions')
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'sakhi' => SakhiCircle::with('village')
                ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('leader_name', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),

            'village_reports' => VillageReport::with(['village', 'user'])
                ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%"))
                ->orderBy('created_at', 'desc')
                ->paginate(15)
                ->withQueryString(),
        };

        // Master data for create modal options
        $districts = District::all()->map(fn ($d) => ['id' => $d->id, 'name' => $d->name_en ?? $d->name_gu ?? '', 'code' => $d->code]);
        $villages = Village::limit(50)->get(['id', 'name']);
        $hospitals = Hospital::all(['id', 'name']);

        return Inertia::render('admin/content/manage', [
            'meta' => $meta,
            'items' => $items,
            'filters' => ['search' => $search],
            'districts' => $districts,
            'villages' => $villages,
            'hospitals' => $hospitals,
        ]);
    }

    /**
     * Store a new record for a module.
     */
    public function storeModuleItem(Request $request, string $module): RedirectResponse
    {
        abort_if(! isset(self::MODULE_META[$module]), 404);

        match ($module) {
            'schemes' => $this->storeScheme($request),
            'scholarships' => $this->storeScholarship($request),
            'jobs' => $this->storeJob($request),
            'health' => $this->storeHealthCamp($request),
            'blood' => $this->storeBloodRequest($request),
            'mock_tests' => $this->storeMockTest($request),
            'sakhi' => $this->storeSakhiCircle($request),
            'village_reports' => $this->storeVillageReport($request),
        };

        return back()->with('success', self::MODULE_META[$module]['singular'].' created successfully.');
    }

    /**
     * Toggle active/published status of an item.
     */
    public function toggleModuleItem(Request $request, string $module, int $id): RedirectResponse
    {
        abort_if(! isset(self::MODULE_META[$module]), 404);

        match ($module) {
            'schemes' => $this->toggleScheme($id),
            'scholarships' => $this->toggleScholarship($id),
            'jobs' => $this->toggleJob($id),
            'health' => $this->toggleHealthCamp($id),
            'blood' => $this->toggleBlood($id),
            'mock_tests' => $this->toggleMockTest($id),
            'sakhi' => $this->toggleSakhi($id),
            'village_reports' => $this->toggleVillageReport($id),
        };

        return back()->with('success', 'Status updated.');
    }

    // ─── Private Store Methods ──────────────────────────────────────────────

    private function storeScheme(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:schemes,slug'],
            'benefit_summary' => ['required', 'string', 'max:500'],
            'documents' => ['nullable', 'string'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $docs = ! empty($validated['documents'])
            ? array_map('trim', explode(',', $validated['documents']))
            : ['Aadhaar Card', 'Caste Certificate'];

        Scheme::create([
            'slug' => $slug,
            'is_published' => true,
            'benefits' => [
                'title' => $validated['title'],
                'benefit' => $validated['benefit_summary'],
            ],
            'required_documents' => $docs,
            'eligibility_rules' => ['community' => ['ST']],
            'process_steps' => ['Submit application online or at Taluka office', 'Physical verification by field coordinator'],
        ]);
    }

    private function storeScholarship(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:scholarships,slug'],
            'amount' => ['required', 'numeric', 'min:0'],
            'deadline_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        $deadline = now()->addDays((int) ($validated['deadline_days'] ?? 30));

        Scholarship::create([
            'slug' => $slug,
            'amount' => $validated['amount'],
            'deadline_at' => $deadline,
            'is_published' => true,
            'eligibility_rules' => [
                'title' => $validated['title'],
                'community' => ['ST'],
            ],
        ]);
    }

    private function storeJob(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'salary_range' => ['nullable', 'string', 'max:100'],
            'requirements' => ['nullable', 'string'],
        ]);

        $reqs = ! empty($validated['requirements'])
            ? array_map('trim', explode(',', $validated['requirements']))
            : ['10th / 12th Pass', 'Local tribal resident preferred'];

        JobPosting::create([
            'title' => $validated['title'],
            'company' => $validated['company'],
            'location' => $validated['location'],
            'salary_range' => $validated['salary_range'] ?? 'Negotiable',
            'requirements' => $reqs,
            'deadline_at' => now()->addDays(30),
            'is_active' => true,
        ]);
    }

    private function storeHealthCamp(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'address' => ['required', 'string', 'max:500'],
            'scheduled_at' => ['required', 'date'],
        ]);

        HealthCamp::create([
            'title' => $validated['title'],
            'organizer' => $validated['organizer'] ?? 'GGVT Health Mission',
            'district_id' => $validated['district_id'] ?? null,
            'address' => $validated['address'],
            'scheduled_at' => $validated['scheduled_at'],
            'is_active' => true,
        ]);
    }

    private function storeBloodRequest(Request $request): void
    {
        $validated = $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'blood_group' => ['required', 'string', 'max:10'],
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
            'units_required' => ['required', 'integer', 'min:1'],
            'contact_phone' => ['required', 'string', 'max:20'],
        ]);

        BloodRequest::create([
            'patient_name' => $validated['patient_name'],
            'blood_group' => $validated['blood_group'],
            'hospital_id' => $validated['hospital_id'] ?? null,
            'units_required' => $validated['units_required'],
            'contact_phone' => $validated['contact_phone'],
            'urgency' => 'urgent',
            'status' => 'active',
        ]);
    }

    private function storeMockTest(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:240'],
            'total_marks' => ['required', 'integer', 'min:10', 'max:500'],
        ]);

        MockTest::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'is_active' => true,
        ]);
    }

    private function storeSakhiCircle(Request $request): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'leader_name' => ['required', 'string', 'max:255'],
            'leader_phone' => ['required', 'string', 'max:20'],
            'members_count' => ['required', 'integer', 'min:2'],
        ]);

        SakhiCircle::create([
            'name' => $validated['name'],
            'leader_name' => $validated['leader_name'],
            'leader_phone' => $validated['leader_phone'],
            'members_count' => $validated['members_count'],
            'is_active' => true,
        ]);
    }

    private function storeVillageReport(Request $request): void
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'village_id' => ['required', 'exists:villages,id'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:10'],
        ]);

        VillageReport::create([
            'title' => $validated['title'],
            'village_id' => $validated['village_id'],
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);
    }

    // ─── Private Toggle Methods ─────────────────────────────────────────────

    private function toggleScheme(int $id): void
    {
        $item = Scheme::findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);
    }

    private function toggleScholarship(int $id): void
    {
        $item = Scholarship::findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);
    }

    private function toggleJob(int $id): void
    {
        $item = JobPosting::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    private function toggleHealthCamp(int $id): void
    {
        $item = HealthCamp::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    private function toggleBlood(int $id): void
    {
        $item = BloodRequest::findOrFail($id);
        $newStatus = $item->status === 'active' ? 'fulfilled' : 'active';
        $item->update(['status' => $newStatus]);
    }

    private function toggleMockTest(int $id): void
    {
        $item = MockTest::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    private function toggleSakhi(int $id): void
    {
        $item = SakhiCircle::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
    }

    private function toggleVillageReport(int $id): void
    {
        $item = VillageReport::findOrFail($id);
        $next = match ($item->status) {
            'pending' => 'investigating',
            'investigating' => 'resolved',
            default => 'pending',
        };
        $item->update(['status' => $next]);
    }
}
