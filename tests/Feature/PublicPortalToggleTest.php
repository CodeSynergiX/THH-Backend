<?php

namespace Tests\Feature;

use App\Domains\Settings\Models\Setting;
use App\Models\User;
use Database\Seeders\ContentRegistrySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicPortalToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeAndLocalizationSeeder::class);
        $this->seed(ContentRegistrySeeder::class);
        PermissionSeeder::syncCatalog();

        Role::findOrCreate('super_admin');
        Role::findOrCreate('admin');
        Role::findOrCreate('staff');
        Role::findOrCreate('citizen');
    }

    public function test_when_public_portal_is_enabled_public_routes_are_accessible(): void
    {
        Setting::set('public_portal_enabled', true, 'general');

        $response = $this->get('/');
        $response->assertStatus(200);

        $aboutResponse = $this->get('/about');
        $aboutResponse->assertStatus(200);
    }

    public function test_when_public_portal_is_disabled_root_redirects_guest_to_login(): void
    {
        Setting::set('public_portal_enabled', false, 'general');

        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_when_public_portal_is_disabled_root_redirects_authenticated_admin_to_dashboard(): void
    {
        Setting::set('public_portal_enabled', false, 'general');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_when_public_portal_is_disabled_sub_routes_return_404(): void
    {
        Setting::set('public_portal_enabled', false, 'general');

        $this->get('/about')->assertStatus(404);
        $this->get('/privacy')->assertStatus(404);
        $this->get('/terms')->assertStatus(404);
        $this->get('/track/THH-2026-00001')->assertStatus(404);
        $this->get('/community/awas')->assertStatus(404);
    }

    public function test_admin_can_update_public_portal_toggle_in_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Initially disabled
        Setting::set('public_portal_enabled', false, 'general');
        $this->assertFalse((bool) Setting::get('public_portal_enabled'));

        // Toggle ON
        $response = $this->actingAs($admin)->post('/admin/settings/general', [
            'app_name_en' => 'Tribal Helping Hand',
            'app_name_gu' => 'ટ્રાઇબલ હેલ્પિંગ હૅન્ડ',
            'support_email' => 'support@ggvt.org',
            'helpline_phone' => '1800-233-5500',
            'public_portal_enabled' => true,
        ]);

        $response->assertRedirect();
        $this->assertTrue((bool) Setting::get('public_portal_enabled'));

        // Toggle OFF
        $responseOff = $this->actingAs($admin)->post('/admin/settings/general', [
            'app_name_en' => 'Tribal Helping Hand',
            'app_name_gu' => 'ટ્રાઇબલ હેલ્પિંગ હૅન્ડ',
            'support_email' => 'support@ggvt.org',
            'helpline_phone' => '1800-233-5500',
            'public_portal_enabled' => false,
        ]);

        $responseOff->assertRedirect();
        $this->assertFalse((bool) Setting::get('public_portal_enabled'));
    }
}
