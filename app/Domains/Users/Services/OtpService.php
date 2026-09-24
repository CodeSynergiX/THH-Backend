<?php

namespace App\Domains\Users\Services;

use App\Domains\Users\Models\OtpCode;
use App\Mail\OtpCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function issue(?string $phone, ?string $email, string $purpose = 'login'): string
    {
        $code = app()->environment('testing') ? '123456' : (string) random_int(100000, 999999);

        if ($email) {
            $email = strtolower(trim($email));
        }
        if ($phone) {
            $phone = trim($phone);
        }

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
        } elseif ($email && ! $phone) {
            $foundUser = User::query()
                ->where('email', $email)
                ->first();
            if ($foundUser && $foundUser->phone) {
                $phone = trim($foundUser->phone);
            }
        }

        OtpCode::create([
            'phone' => $phone,
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        if ($email) {
            try {
                \App\Domains\Settings\Services\MailSettingsService::apply();
                Mail::to($email)->send(new OtpCodeMail($code, $purpose));
            } catch (\Throwable $e) {
                Log::warning('Failed to send OTP email: '.$e->getMessage());
            }
        }

        return $code;
    }

    public function verify(?string $phone, ?string $email, string $code, string|array|null $purpose = null): bool
    {
        $code = trim($code);
        if ($email) {
            $email = strtolower(trim($email));
        }
        if ($phone) {
            $phone = trim($phone);
        }

        // Auto-resolve associated contact if only one was provided
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
        } elseif ($email && ! $phone) {
            $foundUser = User::query()
                ->where('email', $email)
                ->first();
            if ($foundUser && $foundUser->phone) {
                $phone = trim($foundUser->phone);
            }
        }

        $query = OtpCode::query()->where('expires_at', '>', now())->latest();

        if ($email && $phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $query->where(function ($q) use ($email, $phone, $cleanPhone) {
                $q->where('email', $email)
                    ->orWhere('phone', $phone);
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere('phone', $last10)->orWhere('phone', '+91'.$last10)->orWhere('phone', '0'.$last10);
                }
            });
        } elseif ($email) {
            $query->where('email', $email);
        } elseif ($phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $query->where(function ($q) use ($phone, $cleanPhone) {
                $q->where('phone', $phone);
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhere('phone', $last10)->orWhere('phone', '+91'.$last10)->orWhere('phone', '0'.$last10);
                }
            });
        } else {
            return false;
        }

        if ($purpose) {
            if (is_array($purpose)) {
                $query->whereIn('purpose', $purpose);
            } else {
                $query->where('purpose', $purpose);
            }
        }

        $records = $query->get();
        if ($records->isEmpty()) {
            return false;
        }

        foreach ($records as $record) {
            if (Hash::check($code, $record->code_hash)) {
                $record->delete();

                return true;
            }
            $record->increment('attempts');
        }

        return false;
    }
}
