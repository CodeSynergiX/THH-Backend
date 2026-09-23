<?php

use App\Domains\Users\Models\OtpCode;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('citizen');
});

test('citizen can request an OTP', function () {
    $response = $this->postJson('/api/v1/auth/otp/request', [
        'phone' => '9876543210',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'OTP sent successfully.',
        ]);

    expect(OtpCode::where('phone', '9876543210')->exists())->toBeTrue();
});

test('citizen can verify OTP and receive Sanctum auth token', function () {
    $this->postJson('/api/v1/auth/otp/request', [
        'phone' => '9876543210',
    ]);

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'phone' => '9876543210',
        'code' => '123456',
        'name' => 'Ramesh Patel',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'phone', 'roles'],
            ],
        ]);

    $user = User::where('phone', '9876543210')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole('citizen'))->toBeTrue();
});

test('invalid OTP code fails verification', function () {
    $this->postJson('/api/v1/auth/otp/request', [
        'phone' => '9876543210',
    ]);

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'phone' => '9876543210',
        'code' => '999999',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid or expired OTP code.',
        ]);
});

test('citizen can log in with password using mobile number', function () {
    $user = User::factory()->create([
        'phone' => '9825000014',
        'email' => '9825000014@ggvt.org',
        'password' => Hash::make('Password@123'),
    ]);
    $user->assignRole('citizen');

    // Test with plain 10-digit phone
    $response = $this->postJson('/api/v1/auth/login', [
        'phone' => '9825000014',
        'password' => 'Password@123',
    ]);
    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'user']]);

    // Test with identifier field
    $response2 = $this->postJson('/api/v1/auth/login', [
        'identifier' => '9825000014',
        'password' => 'Password@123',
    ]);
    $response2->assertOk()->assertJsonPath('success', true);

    // Test with email field carrying phone number
    $response3 = $this->postJson('/api/v1/auth/login', [
        'email' => '9825000014',
        'password' => 'Password@123',
    ]);
    $response3->assertOk()->assertJsonPath('success', true);
});

test('citizen can request and verify OTP using email', function () {
    $user = User::factory()->create([
        'phone' => '9825000099',
        'email' => 'citizen.test@ggvt.org',
    ]);
    $user->assignRole('citizen');

    $reqResponse = $this->postJson('/api/v1/auth/otp/request', [
        'email' => 'citizen.test@ggvt.org',
    ]);
    $reqResponse->assertOk()
        ->assertJsonPath('success', true);

    $verifyResponse = $this->postJson('/api/v1/auth/otp/verify', [
        'email' => 'citizen.test@ggvt.org',
        'code' => '123456',
    ]);
    $verifyResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

test('requesting OTP with phone sends email to registered user email', function () {
    $user = User::factory()->create([
        'phone' => '9825000088',
        'email' => 'registered.user@ggvt.org',
    ]);
    $user->assignRole('citizen');

    $reqResponse = $this->postJson('/api/v1/auth/otp/request', [
        'phone' => '9825000088',
    ]);
    $reqResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'registered.user@ggvt.org');

    $verifyResponse = $this->postJson('/api/v1/auth/otp/verify', [
        'phone' => '9825000088',
        'code' => '123456',
    ]);
    $verifyResponse->assertOk()
        ->assertJsonPath('success', true);
});
