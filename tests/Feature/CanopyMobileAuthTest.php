<?php

use App\Domains\Cases\Models\Application;
use App\Domains\Content\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ThemeAndLocalizationSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('citizen');
    Role::findOrCreate('mentor');
    Role::findOrCreate('volunteer');
    Role::findOrCreate('admin');
    PermissionSeeder::syncCatalog();
});

test('citizen can register with first last email and mobile', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Rajesh',
        'last_name' => 'Patel',
        'email' => 'rajesh@example.com',
        'phone' => '9825100001',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'role' => 'citizen',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user.first_name', 'Rajesh')
        ->assertJsonPath('data.user.name', 'Rajesh Patel');

    $user = User::where('email', 'rajesh@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('citizen'))->toBeTrue();
    expect($user->helper_status)->toBeNull();
});

test('mentor register is pending until admin approves', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'first_name' => 'Sevak',
        'last_name' => 'Bhai',
        'email' => 'sevak@example.com',
        'phone' => '9825100002',
        'password' => 'Secret123!',
        'password_confirmation' => 'Secret123!',
        'role' => 'mentor',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user.helper_status', 'pending');

    $mentor = User::where('email', 'sevak@example.com')->first();
    expect($mentor->hasRole('mentor'))->toBeTrue();
    expect($mentor->isApprovedHelper())->toBeFalse();

    $this->actingAs($mentor, 'sanctum')
        ->getJson('/api/v1/helper/cases')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);

    $admin = User::factory()->create(['phone' => '9825100099']);
    $admin->assignRole('admin');
    $this->actingAs($admin)->post("/admin/people/{$mentor->id}/helper-status", [
        'helper_status' => 'approved',
    ])->assertRedirect();

    expect($mentor->fresh()->helper_status)->toBe('approved');
    expect($mentor->fresh()->isApprovedHelper())->toBeTrue();
});

test('otp password reset updates the password', function () {
    $user = User::factory()->create([
        'email' => 'resetme@example.com',
        'phone' => '9825100003',
        'password' => 'OldPass123!',
    ]);

    $this->postJson('/api/v1/auth/otp/request', [
        'phone' => '9825100003',
        'purpose' => 'reset',
    ])->assertOk();

    $this->postJson('/api/v1/auth/reset-password', [
        'phone' => '9825100003',
        'code' => '123456',
        'password' => 'NewPass123!',
        'password_confirmation' => 'NewPass123!',
        'purpose' => 'reset',
    ])->assertOk();

    $this->postJson('/api/v1/auth/login', [
        'email' => 'resetme@example.com',
        'password' => 'NewPass123!',
    ])->assertOk()->assertJsonPath('success', true);
});

test('profile extra fields can be updated but email stays', function () {
    $user = User::factory()->create([
        'email' => 'profile@example.com',
        'phone' => '9825100004',
        'first_name' => 'Asha',
        'last_name' => 'Gamit',
        'name' => 'Asha Gamit',
    ]);
    $user->assignRole('citizen');

    $this->actingAs($user, 'sanctum')->putJson('/api/v1/me', [
        'first_name' => 'Ashaben',
        'last_name' => 'Gamit',
        'gender' => 'female',
        'date_of_birth' => '1992-03-12',
        'blood_group' => 'B+',
        'address' => 'Ward No. 3, Near Temple',
        'pincode' => '396521',
        'ration_card_no' => 'GJ-24-CKL-998811',
        'blood_donor_active' => true,
        'sms_alerts_active' => true,
    ])->assertOk()
        ->assertJsonPath('data.first_name', 'Ashaben')
        ->assertJsonPath('data.blood_group', 'B+')
        ->assertJsonPath('data.address', 'Ward No. 3, Near Temple')
        ->assertJsonPath('data.pincode', '396521')
        ->assertJsonPath('data.ration_card_no', 'GJ-24-CKL-998811')
        ->assertJsonPath('data.email', 'profile@example.com');
});

test('user can upload avatar via base64 or file', function () {
    $user = User::factory()->create(['phone' => '9825100077']);
    $user->assignRole('citizen');

    $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/me/avatar', [
        'avatar_base64' => $fakeBase64,
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['avatar_url', 'user']]);

    expect($user->fresh()->avatar_url)->not->toBeNull();
});

test('published theme uses living canopy primary for mobile clients', function () {
    $this->seed(ThemeAndLocalizationSeeder::class);

    $this->getJson('/api/v1/config/theme')
        ->assertOk()
        ->assertJsonPath('data.light.primary', '#006026');
});

test('pending mentor cannot see unassigned cases', function () {
    $citizen = User::factory()->create(['phone' => '9825100005']);
    $citizen->assignRole('citizen');
    $mentor = User::factory()->create([
        'phone' => '9825100006',
        'helper_status' => 'pending',
    ]);
    $mentor->assignRole('mentor');

    $category = Category::create([
        'slug' => 'housing',
        'icon' => 'home',
        'is_active' => true,
    ]);

    $app = Application::create([
        'case_no' => 'THH-2026-00999',
        'user_id' => $citizen->id,
        'category_id' => $category->id,
        'title' => 'Roof leak',
        'description' => 'Need roof repair after monsoon.',
        'urgency' => 'urgent',
        'status' => Application::STATUS_RECEIVED,
        'lat' => 22.7,
        'lng' => 71.6,
    ]);

    $this->actingAs($mentor, 'sanctum')
        ->getJson('/api/v1/helper/cases')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);

    $mentor->update(['helper_status' => 'approved']);
    $app->update(['current_assignee_id' => $mentor->id]);

    $this->actingAs($mentor->fresh(), 'sanctum')
        ->getJson('/api/v1/helper/cases')
        ->assertOk()
        ->assertJsonPath('meta.total', 1);
});
