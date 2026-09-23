<?php

namespace App\Domains\Settings;

use App\Domains\Settings\Models\Setting;
use Illuminate\Support\Facades\Storage;

class Branding
{
    public static function payload(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $nameEn = (string) (Setting::get('app_name_en') ?: 'Tribal Helping Hand');
        $nameGu = (string) (Setting::get('app_name_gu') ?: $nameEn);

        // English short name
        $shortEn = (string) (Setting::get('app_name_short_en') ?: '');
        if (! $shortEn) {
            // Fallback: legacy key → initials from English name
            $shortEn = (string) (Setting::get('app_name_short') ?: '');
            if (! $shortEn) {
                $words = preg_split('/\s+/', trim($nameEn));
                $shortEn = count($words) > 1
                    ? implode('', array_map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)), $words))
                    : $nameEn;
            }
        }

        // Gujarati short name — defaults to English short name when not set
        $shortGu = (string) (Setting::get('app_name_short_gu') ?: $shortEn);

        $path = Setting::get('app_logo_path');
        $faviconPath = Setting::get('app_favicon_path');

        return [
            'name' => $locale === 'gu' ? $nameGu : $nameEn,
            'name_en' => $nameEn,
            'name_gu' => $nameGu,
            'name_short' => $locale === 'gu' ? $shortGu : $shortEn,
            'name_short_en' => $shortEn,
            'name_short_gu' => $shortGu,
            'logo_url' => self::logoUrl(is_string($path) ? $path : null),
            'favicon_url' => self::logoUrl(is_string($faviconPath) ? $faviconPath : null),
            'helpline' => Setting::get('helpline_phone') ?: '1800-233-5500',
            'support_email' => Setting::get('support_email') ?: 'support@ggvt.org',
            'public_portal_enabled' => (bool) Setting::get('public_portal_enabled', true),
        ];
    }

    public static function logoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function faviconUrl(): ?string
    {
        $path = Setting::get('app_favicon_path');

        return self::logoUrl(is_string($path) ? $path : null);
    }
}
