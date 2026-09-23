<?php

namespace Tests\Feature;

use App\Domains\Content\Models\ContentItem;
use App\Domains\Content\Models\ContentModule;
use App\Models\User;
use Database\Seeders\ContentRegistrySeeder;
use Database\Seeders\NotificationSeeder;
use Database\Seeders\PermissionSeeder;
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
        PermissionSeeder::syncCatalog();
        $this->seed(ContentRegistrySeeder::class);

        Role::findOrCreate('super_admin');
        Role::findOrCreate('admin');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_content_module_management_pages(): void
    {
        $modules = ContentModule::query()->pluck('slug');

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
        $this->assertDatabaseHas('content_items', ['slug' => 'van-bandhu-kalyan', 'is_published' => true]);

        $item = ContentItem::where('slug', 'van-bandhu-kalyan')->first();
        $toggleResponse = $this->actingAs($this->admin)->post("/admin/content/schemes/{$item->id}/toggle");
        $toggleResponse->assertRedirect();
        $this->assertFalse((bool) $item->fresh()->is_published);
    }

    public function test_admin_can_create_and_delete_module(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/content/modules', [
            'title_en' => 'Forest Rights Desk',
            'title_gu' => 'Jungle Hak Desk',
            'description_en' => 'FRA claims support',
            'description_gu' => 'FRA claims',
            'slug' => 'forest-rights',
            'is_public' => true,
            'is_enabled' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_modules', ['slug' => 'forest-rights']);
    }
}
