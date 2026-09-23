<?php

namespace Tests\Feature;

use App\Domains\Users\Models\OtpCode;
use App\Domains\Users\Services\OtpService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('otp is 6 digits and can verify for login', function () {
    $user = User::factory()->create([
        'email' => 'random_test@ggvt.org',
        'phone' => '9825199991',
        'password' => 'Initial123!',
    ]);

    $service = app(OtpService::class);
    $code = $service->issue($user->phone, $user->email, 'login');

    expect(strlen($code))->toBe(6);
    expect(is_numeric($code))->toBeTrue();

    // Verify OTP succeeds with correct code
    $verified = $service->verify($user->phone, $user->email, $code, 'login');
    expect($verified)->toBeTrue();

    // Verify OTP fails after being consumed/deleted
    $retry = $service->verify($user->phone, $user->email, $code, 'login');
    expect($retry)->toBeFalse();
});

test('forgot password otp reset updates user password and allows login', function () {
    $user = User::factory()->create([
        'email' => 'forgot_test@ggvt.org',
        'phone' => '9825199992',
        'password' => 'OldPassword123!',
    ]);

    // Request reset OTP
    $reqResponse = $this->postJson('/api/v1/auth/otp/request', [
        'email' => 'forgot_test@ggvt.org',
        'purpose' => 'reset',
    ]);
    $reqResponse->assertOk();

    $otpRecord = OtpCode::where('email', 'forgot_test@ggvt.org')
        ->where('purpose', 'reset')
        ->latest()
        ->first();
    expect($otpRecord)->not->toBeNull();

    // Create a known random code to test end-to-end reset
    $testCode = (string) random_int(100000, 999999);
    $otpRecord->update(['code_hash' => Hash::make($testCode)]);

    // Reset password using the random OTP
    $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'forgot_test@ggvt.org',
        'code' => $testCode,
        'password' => 'BrandNewPass123!',
        'password_confirmation' => 'BrandNewPass123!',
        'purpose' => 'reset',
    ]);
    $resetResponse->assertOk()->assertJsonPath('success', true);

    // Verify login with new password works
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'forgot_test@ggvt.org',
        'password' => 'BrandNewPass123!',
    ]);
    $loginResponse->assertOk()->assertJsonPath('success', true);
});

test('reset password with phone number and random otp works with variations', function () {
    $user = User::factory()->create([
        'email' => 'phone_user@ggvt.org',
        'phone' => '9825199993',
        'password' => 'OldPassword123!',
    ]);

    $service = app(OtpService::class);
    $code = (string) random_int(100000, 999999);

    OtpCode::create([
        'phone' => '9825199993',
        'email' => 'phone_user@ggvt.org',
        'purpose' => 'reset',
        'code_hash' => Hash::make($code),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);

    // Reset using +91 formatted phone
    $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
        'phone' => '+919825199993',
        'code' => $code,
        'password' => 'PhoneNewPass123!',
        'password_confirmation' => 'PhoneNewPass123!',
        'purpose' => 'reset',
    ]);
    $resetResponse->assertOk()->assertJsonPath('success', true);

    // Verify login works with new password
    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'identifier' => '9825199993',
        'password' => 'PhoneNewPass123!',
    ]);
    $loginResponse->assertOk()->assertJsonPath('success', true);
});
