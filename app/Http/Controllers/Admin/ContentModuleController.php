<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\VillageReport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentModuleController extends Controller
{
    /**
     * Content & Community Modules Dashboard.
     */
    public function index(Request $request): Response
    {
        $modules = [
            [
                'slug' => 'schemes',
                'title' => 'Government Schemes',
                'title_gu' => 'સરકારી યોજનાઓ',
                'count' => Scheme::count(),
                'active_count' => Scheme::where('is_active', true)->count(),
                'description' => 'Awas, Ayushman, Kisan Sahay, Forest Rights eligibility directory.',
            ],
            [
                'slug' => 'scholarships',
                'title' => 'Scholarships',
                'title_gu' => 'છાત્રવૃત્તિ સહાય',
                'count' => Scholarship::count(),
                'active_count' => Scholarship::where('is_active', true)->count(),
                'description' => 'Tribal welfare post-matric and higher education scholarships.',
            ],
            [
                'slug' => 'jobs',
                'title' => 'Job Postings',
                'title_gu' => 'રોજગાર તકો',
                'count' => JobPosting::count(),
                'active_count' => JobPosting::where('is_active', true)->count(),
                'description' => 'Verified local vocational, government, and apprenticeship vacancies.',
            ],
            [
                'slug' => 'health',
                'title' => 'Health Camps & Hospitals',
                'title_gu' => 'આરોગ્ય શિબિર અને દવાખાના',
                'count' => HealthCamp::count(),
                'active_count' => HealthCamp::count(),
                'description' => 'Mobile medical units, sickle cell screening, and hospital directories.',
            ],
            [
                'slug' => 'blood',
                'title' => 'Emergency Blood Requests',
                'title_gu' => 'ઇમરજન્સી રક્ત સહાય',
                'count' => BloodRequest::count(),
                'active_count' => BloodRequest::where('status', 'open')->count(),
                'description' => 'Urgent blood donor matching across tribal talukas.',
            ],
            [
                'slug' => 'mock_tests',
                'title' => 'Mock Tests & Study Material',
                'title_gu' => 'મોક ટેસ્ટ અને અભ્યાસ સાહિત્ય',
                'count' => MockTest::count(),
                'active_count' => MockTest::where('is_active', true)->count(),
                'description' => 'Competitive exam prep (GPSC, Police, Forest Guard) in Gujarati.',
            ],
            [
                'slug' => 'sakhi',
                'title' => 'Sakhi Circles (SHGs)',
                'title_gu' => 'સખી મંડળ પ્રવૃત્તિ',
                'count' => SakhiCircle::count(),
                'active_count' => SakhiCircle::count(),
                'description' => 'Self-help groups, organic tribal handicrafts and micro-credit.',
            ],
            [
                'slug' => 'village_reports',
                'title' => 'Village Infrastructure Reports',
                'title_gu' => 'ગામ પ્રશ્નો અને ઉકેલ',
                'count' => VillageReport::count(),
                'active_count' => VillageReport::where('status', 'pending')->count(),
                'description' => 'Citizen ground reports on drinking water, electricity and roads.',
            ],
        ];

        return Inertia::render('admin/content/index', [
            'modules' => $modules,
        ]);
    }
}
