<?php

namespace Tests\Feature;

use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentModuleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeAndLocalizationSeeder::class);
        $this->seed(NotificationSeeder::class);

        Role::findOrCreate('super_admin');
        Role::findOrCreate('admin');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_content_module_management_pages(): void
    {
        $modules = ['schemes', 'scholarships', 'jobs', 'health', 'blood', 'mock_tests', 'sakhi', 'village_reports'];

        foreach ($modules as $module) {
            $response = $this->actingAs($this->admin)->get("/admin/content/{$module}");
            $response->assertStatus(200);
        }
    }

    public function test_admin_can_create_and_toggle_scheme(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/content/schemes', [
            'title' => 'Van Bandhu Kalyan Yojana',
            'slug' => 'van-bandhu-kalyan',
            'benefit_summary' => 'Comprehensive development package for tribal talukas.',
            'documents' => 'Aadhaar, Caste Certificate',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('schemes', ['slug' => 'van-bandhu-kalyan', 'is_published' => true]);

        $scheme = Scheme::where('slug', 'van-bandhu-kalyan')->first();
        $toggleResponse = $this->actingAs($this->admin)->post("/admin/content/schemes/{$scheme->id}/toggle");
        $toggleResponse->assertRedirect();
        $this->assertFalse((bool) $scheme->fresh()->is_published);
    }

    public function test_admin_can_create_and_toggle_job(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/content/jobs', [
            'title' => 'Forest Guard Trainee',
            'company' => 'Gujarat Forest Department',
            'location' => 'Dang Forest Division',
            'salary_range' => '₹19,900 - ₹63,200',
            'requirements' => '12th Pass, Physical Fitness',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_postings', ['title' => 'Forest Guard Trainee', 'is_active' => true]);

        $job = JobPosting::where('title', 'Forest Guard Trainee')->first();
        $toggleResponse = $this->actingAs($this->admin)->post("/admin/content/jobs/{$job->id}/toggle");
        $toggleResponse->assertRedirect();
        $this->assertFalse((bool) $job->fresh()->is_active);
    }

    public function test_admin_can_cycle_village_report_status(): void
    {
        $district = District::create(['name_en' => 'Dang', 'name_gu' => 'ડાંગ', 'code' => 'DNG']);
        $taluka = Taluka::create(['district_id' => $district->id, 'name_en' => 'Ahwa', 'name_gu' => 'આહવા']);
        $village = Village::create(['taluka_id' => $taluka->id, 'name_en' => 'Subir', 'name_gu' => 'સુબીર']);

        $report = VillageReport::create([
            'village_id' => $village->id,
            'user_id' => $this->admin->id,
            'title' => 'Drinking water pipeline broken',
            'category' => 'water',
            'description' => 'Main pipeline near school broken, needs immediate repair.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)->post("/admin/content/village_reports/{$report->id}/toggle");
        $this->assertEquals('investigating', $report->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/content/village_reports/{$report->id}/toggle");
        $this->assertEquals('resolved', $report->fresh()->status);
    }
}
