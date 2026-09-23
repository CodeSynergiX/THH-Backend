<?php

use App\Domains\Cases\Models\Application;
use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\Hospital;
use App\Domains\Content\Models\Scheme;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Database\Seeders\ContentRegistrySeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('demo data seeder populates realistic tribal records across all modules', function () {
    $this->seed(ThemeAndLocalizationSeeder::class);
    $this->seed(DemoDataSeeder::class);

    // 1. Districts and Locations
    expect(District::count())->toBe(5)
        ->and(District::where('code', 'DANG')->exists())->toBeTrue()
        ->and(District::where('code', 'DAHOD')->exists())->toBeTrue()
        ->and(District::where('code', 'NARMADA')->exists())->toBeTrue()
        ->and(District::where('code', 'CHHOTA_UDEPUR')->exists())->toBeTrue()
        ->and(District::where('code', 'TAPI')->exists())->toBeTrue();

    expect(Village::count())->toBeGreaterThanOrEqual(15);

    // 2. Categories & Subcategories
    expect(Category::count())->toBe(8)
        ->and(Category::where('slug', 'schemes')->exists())->toBeTrue()
        ->and(Category::where('slug', 'forest_rights')->exists())->toBeTrue()
        ->and(Category::where('slug', 'scholarships')->exists())->toBeTrue();

    // 3. Key Users with proper roles
    $admin = User::where('email', 'admin@ggvt.org')->first();
    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('admin'))->toBeTrue()
        ->and($admin->hasRole('super_admin'))->toBeTrue();

    $collector = User::where('email', 'collector.dang@ggvt.org')->first();
    expect($collector)->not->toBeNull()
        ->and($collector->hasRole('collector'))->toBeTrue();

    $sevak = User::where('email', 'sevak.ahwa@ggvt.org')->first();
    expect($sevak)->not->toBeNull()
        ->and($sevak->hasRole('staff'))->toBeTrue();

    $mentor = User::where('email', 'mentor.education@ggvt.org')->first();
    expect($mentor)->not->toBeNull()
        ->and($mentor->hasRole('mentor'))->toBeTrue();

    $citizens = User::whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->get();
    expect($citizens->count())->toBeGreaterThanOrEqual(7);

    // 4. Applications across all lifecycle states
    $applications = Application::all();
    expect($applications->count())->toBe(16);

    $statuses = $applications->pluck('status')->unique()->values()->all();
    expect($statuses)->toContain(
        Application::STATUS_RECEIVED,
        Application::STATUS_VERIFICATION,
        Application::STATUS_CATEGORISED,
        Application::STATUS_ASSIGNED,
        Application::STATUS_ASSISTANCE,
        Application::STATUS_FOLLOW_UP,
        Application::STATUS_RESOLVED,
        Application::STATUS_NEED_MORE_INFO,
        Application::STATUS_ON_HOLD,
        Application::STATUS_REJECTED,
        Application::STATUS_REOPENED
    );

    // Case ID format THH-{YYYY}-{00001}
    foreach ($applications as $app) {
        expect($app->case_no)->toMatch('/^THH-\d{4}-\d{5}$/');
    }

    // 5. Content Modules
    expect(Scheme::count())->toBeGreaterThanOrEqual(4);
    expect(Hospital::count())->toBe(3);
});

test('demo data seeder is strictly idempotent and does not duplicate records when run multiple times', function () {
    $this->seed(ThemeAndLocalizationSeeder::class);

    // Run 1
    $this->seed(DemoDataSeeder::class);

    $districtsCount1 = District::count();
    $categoriesCount1 = Category::count();
    $usersCount1 = User::count();
    $applicationsCount1 = Application::count();

    // Run 2 (re-seeding on existing data)
    $this->seed(DemoDataSeeder::class);

    expect(District::count())->toBe($districtsCount1)
        ->and(Category::count())->toBe($categoriesCount1)
        ->and(User::count())->toBe($usersCount1)
        ->and(Application::count())->toBe($applicationsCount1);
});

test('public portal displays live counts directly from seeded database', function () {
    $this->seed(ThemeAndLocalizationSeeder::class);
    $this->seed(DemoDataSeeder::class);
    $this->seed(ContentRegistrySeeder::class);

    $response = $this->get('/');
    $response->assertOk();

    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->has('stats.resolved_cases')
        ->where('stats.resolved_cases', 3) // THH-00001, 00009, 00016
        ->has('stats.citizens_helped')
        ->where('stats.citizens_helped', fn ($count) => $count >= 7)
        ->has('stats.villages_covered')
        ->where('stats.villages_covered', fn ($count) => $count >= 15)
        ->has('modules')
    );
});
