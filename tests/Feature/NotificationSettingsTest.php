<?php

namespace Tests\Feature;

use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Settings\Models\Setting;
use App\Models\User;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ThemeAndLocalizationSeeder::class);
        $this->seed(NotificationSeeder::class);
        PermissionSeeder::syncCatalog();

        Role::findOrCreate('super_admin');
        Role::findOrCreate('admin');
    }

    public function test_user_can_retrieve_and_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        // 1. GET preferences
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notification-preferences');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email_enabled', true)
            ->assertJsonPath('data.push_enabled', true);

        // 2. PUT preferences
        $updateResponse = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/notification-preferences', [
                'email_enabled' => false,
                'push_case_status_change' => true,
                'email_case_status_change' => false,
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email_enabled', false)
            ->assertJsonPath('data.email_case_status_change', false);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'email_enabled' => false,
        ]);
    }

    public function test_user_can_view_and_mark_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $log = NotificationLog::create([
            'user_id' => $user->id,
            'channel' => 'in_app',
            'type' => 'case_status_verification',
            'title' => 'Verification Started',
            'body' => 'Your case is under review.',
            'status' => 'sent',
            'read_at' => null,
        ]);

        $listResponse = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/notifications');

        $listResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 1);

        $readResponse = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/notifications/{$log->id}/read");

        $readResponse->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNotNull($log->fresh()->read_at);
    }

    public function test_admin_can_update_general_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)
            ->post('/admin/settings/general', [
                'app_name_en' => 'Tribal Helping Hand NGO',
                'app_name_gu' => 'ટ્રાઇબલ હેલ્પિંગ હેન્ડ એનજીઓ',
                'support_email' => 'admin@ggvt.org',
                'helpline_phone' => '+91 98765 43210',
            ]);

        $response->assertRedirect();
        $this->assertEquals('Tribal Helping Hand NGO', Setting::get('app_name_en'));
    }

    public function test_admin_can_update_notification_channels(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)
            ->post('/admin/settings/notifications', [
                'email_notifications_enabled' => false,
                'push_notifications_enabled' => true,
            ]);

        $response->assertRedirect();
        $this->assertFalse((bool) Setting::get('email_notifications_enabled'));
    }

    public function test_admin_can_toggle_notification_template(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $template = NotificationTemplate::where('event_key', 'case_submitted')->first();
        $this->assertNotNull($template);
        $this->assertTrue($template->is_enabled);

        $response = $this->actingAs($admin)
            ->post("/admin/settings/notification-template/{$template->id}/toggle");

        $response->assertRedirect();
        $this->assertFalse($template->fresh()->is_enabled);
    }
}
