<?php

namespace Tests\Feature;

use App\Domains\Cases\Models\Application;
use App\Domains\Content\Models\Category;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use App\Models\User;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ThemeAndLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'citizen', 'guard_name' => 'web']);
        $this->seed(ThemeAndLocalizationSeeder::class);
    }

    public function test_portal_homepage_renders_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_case_tracker_endpoint_validates_existence(): void
    {
        // 404 for nonexistent case
        $response = $this->getJson('/track/THH-9999-99999');
        $response->assertStatus(404);

        // Create sample application
        $citizen = User::factory()->create();
        $citizen->assignRole('citizen');

        $category = Category::create([
            'slug' => 'schemes',
            'icon' => 'landmark',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $application = Application::create([
            'case_no' => 'THH-2026-00001',
            'user_id' => $citizen->id,
            'category_id' => $category->id,
            'title' => 'Solar Pump Support',
            'description' => 'Need solar pump for irrigation.',
            'urgency' => 'normal',
            'priority' => 'medium',
            'status' => 'received',
        ]);

        $found = $this->getJson('/track/THH-2026-00001');
        $found->assertStatus(200)
            ->assertJsonPath('data.case_no', 'THH-2026-00001')
            ->assertJsonPath('data.status', 'received');
    }

    public function test_admin_theme_manager_renders(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/theme');
        $response->assertStatus(200);
    }

    public function test_admin_can_publish_new_theme_version(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $initialVersion = ThemeVersion::currentPublished();
        $this->assertNotNull($initialVersion);
        $this->assertEquals(1, $initialVersion->version);

        $newLight = $initialVersion->light;
        $newLight['primary'] = '#C2410C';

        $response = $this->actingAs($admin)->post('/admin/theme/publish', [
            'light' => $newLight,
            'dark' => $initialVersion->dark,
            'notes' => 'Updated primary to warm burnt terracotta',
        ]);

        $response->assertRedirect();

        $active = ThemeVersion::currentPublished();
        $this->assertNotNull($active);
        $this->assertEquals(2, $active->version);
        $this->assertEquals('#C2410C', $active->light['primary']);
    }

    public function test_admin_can_rollback_theme_version(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create version 2
        $ver2 = ThemeVersion::create([
            'version' => 2,
            'light' => ['primary' => '#000000'],
            'dark' => ['primary' => '#FFFFFF'],
            'meta' => [],
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Mark version 1 unpublished
        ThemeVersion::where('version', 1)->update(['is_published' => false]);

        // Rollback to version 1
        $ver1 = ThemeVersion::where('version', 1)->firstOrFail();
        $response = $this->actingAs($admin)->post("/admin/theme/rollback/{$ver1->id}");

        $response->assertRedirect();
        $this->assertTrue($ver1->fresh()->is_published);
        $this->assertFalse($ver2->fresh()->is_published);
    }

    public function test_admin_localization_manager_renders_and_updates_translations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/admin/localization');
        $response->assertStatus(200);

        // Update a translation string
        $updateResponse = $this->actingAs($admin)->post('/admin/localization', [
            'group' => 'app',
            'key' => 'portal.title',
            'locale' => 'gu',
            'value' => 'આદિવાસી સહાયક હાથ - જીજીવીટી',
        ]);

        $updateResponse->assertRedirect();

        $this->assertDatabaseHas('translations', [
            'group' => 'app',
            'key' => 'portal.title',
            'locale' => 'gu',
            'value' => 'આદિવાસી સહાયક હાથ - જીજીવીટી',
        ]);
    }

    public function test_missing_keys_scanner_detects_completeness(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->getJson('/admin/localization/scan-missing');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_locale_switcher_updates_session(): void
    {
        $response = $this->get('/locale/en?redirect=/');
        $response->assertRedirect('/');
        $this->assertEquals('en', session('locale'));

        $responseGu = $this->get('/locale/gu?redirect=/');
        $responseGu->assertRedirect('/');
        $this->assertEquals('gu', session('locale'));
    }
}
