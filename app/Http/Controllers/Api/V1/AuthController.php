<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Settings\Services\MailSettingsService;
use App\Domains\Users\Models\DeviceToken;
use App\Domains\Users\Resources\UserResource;
use App\Domains\Users\Services\OtpService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otp) {}

    /**
     * Request an OTP for authentication / registration.
     */
    public function requestOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'max:255'],
            'identifier' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'in:login,track,reset,password_reset,register'],
        ]);

        $input = $validated['email'] ?? $validated['phone'] ?? $validated['identifier'] ?? null;
        if (! $input) {
            return response()->json([
                'success' => false,
                'message' => 'Phone or email is required.',
                'errors' => ['email' => ['Phone or email is required.']],
            ], 422);
        }

        $phone = $validated['phone'] ?? null;
        $email = $validated['email'] ?? null;

        if (! $email && filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
        } elseif (! $phone) {
            $phone = $input;
        }

        $email = $email ? strtolower(trim($email)) : null;
        $phone = $phone ? trim($phone) : null;

        // If phone is provided and email is not, check if a registered user exists with an email
        if (! $email && $phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $foundUser = User::query()
                ->where('phone', $phone)
                ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere('phone', $last10)
                        ->orWhere('phone', '+91'.$last10)
                        ->orWhere('phone', '0'.$last10);
                })
                ->first();

            if ($foundUser && $foundUser->email) {
                $email = strtolower(trim($foundUser->email));
            }
        }

        $code = $this->otp->issue(
            $phone,
            $email,
            $validated['purpose'] ?? 'login'
        );

        $msg = $email ? "OTP sent successfully to {$email}." : 'OTP sent successfully.';

        return response()->json([
            'success' => true,
            'message' => $msg,
            'data' => [
                'phone' => $phone,
                'email' => $email,
                'debug_code' => null,
            ],
        ]);
    }

    /**
     * Verify OTP and return Sanctum authentication token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'identifier' => ['nullable', 'string'],
            'code' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'purpose' => ['nullable', 'string'],
        ]);

        $input = $validated['email'] ?? $validated['phone'] ?? $validated['identifier'] ?? null;
        $phone = $validated['phone'] ?? null;
        $email = $validated['email'] ?? null;

        if (! $email && filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
        } elseif (! $phone) {
            $phone = $input;
        }

        $email = $email ? strtolower(trim($email)) : null;
        $phone = $phone ? trim($phone) : null;

        $code = trim($validated['code']);

        if (! $this->otp->verify($phone, $email, $code, $validated['purpose'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP code.',
                'errors' => ['code' => ['Invalid or expired OTP code.']],
            ], 422);
        }

        $user = null;
        if ($email) {
            $user = User::query()->where('email', $email)->first();
        }
        if (! $user && $phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $user = User::query()
                ->where('phone', $phone)
                ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere('phone', $last10)
                        ->orWhere('phone', '+91'.$last10);
                })
                ->first();
        }

        if (! $user) {
            $user = User::create([
                'phone' => $phone,
                'email' => $email,
                'name' => $validated['name'] ?? ($email ? explode('@', $email)[0] : 'Citizen'),
                'locale' => 'gu',
                'is_active' => true,
                'last_login_at' => now(),
            ]);
        }

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
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'in:male,female,other'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'date_of_birth' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', 'string', 'max:8'],
            'address' => ['nullable', 'string', 'max:255'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'ration_card_no' => ['nullable', 'string', 'max:50'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'blood_donor_active' => ['nullable', 'boolean'],
            'sms_alerts_active' => ['nullable', 'boolean'],
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

        if (! empty($validated['date_of_birth'])) {
            try {
                $validated['date_of_birth'] = Carbon::parse($validated['date_of_birth'])->toDateString();
            } catch (\Throwable) {
                unset($validated['date_of_birth']);
            }
        }

        $user = $request->user();
        $user->fill($validated);
        if (array_key_exists('first_name', $validated) || array_key_exists('last_name', $validated)) {
            $user->syncDisplayName(
                $validated['first_name'] ?? $user->first_name,
                $validated['last_name'] ?? $user->last_name
            );
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Upload or update profile avatar.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['nullable', 'image', 'max:10240'],
            'avatar_base64' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            $user->avatar_url = asset('storage/'.$path);
            $user->save();
        } elseif ($request->filled('avatar_base64')) {
            $data = $request->input('avatar_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                $data = substr($data, strpos($data, ',') + 1);
                $type = strtolower($type[1]);
            } else {
                $type = 'jpg';
            }
            $decoded = base64_decode($data, true);
            if ($decoded !== false) {
                $filename = 'avatar_'.$user->id.'_'.time().'.'.$type;
                Storage::disk('public')->put('avatars/'.$filename, $decoded);
                $user->avatar_url = asset('storage/avatars/'.$filename);
                $user->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'data' => [
                'avatar_url' => $user->avatar_url,
                'user' => new UserResource($user->fresh()),
            ],
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

    public function loginWithPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'identifier' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $input = $validated['identifier'] ?? $validated['email'] ?? $validated['phone'] ?? null;
        if (! $input) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide an email or mobile number.',
                'errors' => ['identifier' => ['Please provide an email or mobile number.']],
            ], 422);
        }

        $user = null;
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $user = User::query()->where('email', $input)->first();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $input);
            $user = User::query()
                ->where('phone', $input)
                ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere('phone', $last10)
                        ->orWhere('phone', '+91'.$last10)
                        ->orWhere('phone', '0'.$last10);
                })
                ->first();

            if (! $user) {
                $user = User::query()->where('email', $input)->first();
            }
        }

        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number/email or password.',
                'errors' => ['password' => ['Invalid mobile number/email or password.']],
            ], 422);
        }

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Authentication successful.',
            'data' => [
                'token' => $user->createToken('mobile-app')->plainTextToken,
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        MailSettingsService::apply();

        $status = Password::sendResetLink(['email' => $validated['email']]);

        return response()->json([
            'success' => $status === Password::RESET_LINK_SENT,
            'message' => __($status),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'in:citizen,mentor,volunteer'],
            'locale' => ['nullable', 'in:gu,en'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'taluka_id' => ['nullable', 'exists:talukas,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
        ]);

        $role = $validated['role'] ?? 'citizen';
        $isSevak = in_array($role, ['mentor', 'volunteer'], true);

        $user = new User([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'locale' => $validated['locale'] ?? 'gu',
            'district_id' => $validated['district_id'] ?? null,
            'taluka_id' => $validated['taluka_id'] ?? null,
            'village_id' => $validated['village_id'] ?? null,
            'is_active' => true,
            'helper_status' => $isSevak ? User::HELPER_PENDING : null,
            'on_duty' => false,
            'last_login_at' => now(),
        ]);
        $user->syncDisplayName($validated['first_name'], $validated['last_name']);
        $user->save();
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'message' => $isSevak
                ? 'Registration received. A coordinator will approve your Sevak profile.'
                : 'Account created successfully.',
            'data' => [
                'token' => $user->createToken('mobile-app')->plainTextToken,
                'user' => new UserResource($user->fresh()),
            ],
        ], 201);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        if ($request->filled('code')) {
            $validated = $request->validate([
                'email' => ['nullable', 'email'],
                'phone' => ['nullable', 'string', 'max:50'],
                'code' => ['required', 'string'],
                'password' => ['required', 'string', 'min:4', 'confirmed'],
            ]);

            if (empty($validated['email']) && empty($validated['phone'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone or email is required.',
                ], 422);
            }

            $email = ! empty($validated['email']) ? strtolower(trim($validated['email'])) : null;
            $phone = ! empty($validated['phone']) ? trim($validated['phone']) : null;
            $code = trim($validated['code']);
            $purpose = $request->input('purpose', 'reset');

            if (! $this->otp->verify(
                $phone,
                $email,
                $code,
                array_values(array_unique(array_filter([$purpose, 'reset', 'password_reset'])))
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP code.',
                ], 422);
            }

            $user = null;
            if ($email) {
                $user = User::query()->where('email', $email)->first();
            }
            if (! $user && $phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                $user = User::query()
                    ->where('phone', $phone)
                    ->when(strlen($cleanPhone) >= 10, function ($q) use ($cleanPhone) {
                        $last10 = substr($cleanPhone, -10);
                        $q->orWhere('phone', $last10)
                            ->orWhere('phone', '+91'.$last10)
                            ->orWhere('phone', '0'.$last10);
                    })
                    ->first();
            }

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account found for that contact.',
                ], 422);
            }

            $user->forceFill(['password' => $validated['password']])->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.',
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($validated, function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save();
        });

        return response()->json([
            'success' => $status === Password::PASSWORD_RESET,
            'message' => __($status),
        ]);
    }
}
