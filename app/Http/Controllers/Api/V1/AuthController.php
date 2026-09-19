<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Users\Models\DeviceToken;
use App\Domains\Users\Models\OtpCode;
use App\Domains\Users\Requests\RequestOtpRequest;
use App\Domains\Users\Requests\VerifyOtpRequest;
use App\Domains\Users\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Request an OTP for authentication / registration.
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');

        // Generate 6-digit OTP (e.g. 123456 in local / test)
        $code = app()->environment('production') ? (string) random_int(100000, 999999) : '123456';

        OtpCode::create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'phone' => $phone,
                // Include code in non-production environments for automated testing and demo ease
                'debug_code' => app()->environment('production') ? null : $code,
            ],
        ]);
    }

    /**
     * Verify OTP and return Sanctum authentication token.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');

        $otpRecord = OtpCode::where('phone', $phone)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otpRecord || ! Hash::check($code, $otpRecord->code_hash)) {
            if ($otpRecord) {
                $otpRecord->increment('attempts');
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.',
                'errors' => ['code' => ['Invalid or expired OTP code.']],
            ], 422);
        }

        // Find or create citizen user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $request->validated('name') ?? 'Citizen',
                'district_id' => $request->validated('district_id'),
                'village_id' => $request->validated('village_id'),
                'locale' => 'gu',
                'is_active' => true,
                'last_login_at' => now(),
            ]
        );

        if (! $user->hasAnyRole(['super_admin', 'admin', 'staff', 'mentor', 'volunteer', 'partner', 'citizen'])) {
            $user->assignRole('citizen');
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful.',
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Logout and revoke current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }

    /**
     * Get authenticated profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved.',
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * Update profile details.
     */
    public function updateMe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female,other'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'exists:talukas,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'education' => ['nullable', 'string', 'max:100'],
            'income_category' => ['nullable', 'string', 'max:50'],
            'community' => ['nullable', 'string', 'max:100'],
            'locale' => ['nullable', 'in:gu,en'],
            'theme_preference' => ['nullable', 'in:system,light,dark'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Register or update FCM device token.
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'in:android,ios,web'],
            'locale' => ['nullable', 'string', 'max:10'],
        ]);

        $device = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? 'android',
                'locale' => $validated['locale'] ?? $request->user()->locale ?? 'gu',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully.',
            'data' => $device,
        ]);
    }
}
