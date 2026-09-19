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
