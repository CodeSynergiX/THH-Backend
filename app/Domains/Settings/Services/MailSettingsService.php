<?php

namespace App\Domains\Settings\Services;

use App\Domains\Settings\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class MailSettingsService
{
    public const CACHE_KEY = 'thh:smtp_settings';

    /**
     * Fetch SMTP settings from DB (cached) and apply them to Laravel's runtime mail configuration.
     *
     * @return bool True if SMTP settings from admin panel were successfully applied, false otherwise.
     */
    public static function apply(): bool
    {
        try {
            if (! Schema::hasTable('settings')) {
                return false;
            }

            $smtp = self::getSettings();

            $host = trim((string) ($smtp['smtp_host'] ?? ''));
            if ($host === '') {
                return false;
            }

            $port = (int) ($smtp['smtp_port'] ?? 587);
            $scheme = ($port === 465) ? 'smtps' : 'smtp';
            $encryption = ($port === 465) ? 'ssl' : 'tls';

            $rawPw = $smtp['smtp_password_encrypted'] ?? $smtp['smtp_password'] ?? '';
            $password = '';
            if (! empty($rawPw)) {
                try {
                    $password = decrypt($rawPw);
                } catch (\Throwable) {
                    $password = (string) $rawPw;
                }
            }

            $username = (string) ($smtp['smtp_username'] ?? '');
            $fromName = (string) ($smtp['smtp_from_name'] ?? config('mail.from.name', 'Tribal Helping Hand (GGVT)'));
            $fromEmail = (string) ($smtp['smtp_from_email'] ?? config('mail.from.address', 'noreply@ggvt.org'));

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', $port);
            Config::set('mail.mailers.smtp.scheme', $scheme);
            Config::set('mail.mailers.smtp.encryption', $encryption);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);

            if ($fromEmail !== '') {
                Config::set('mail.from.address', $fromEmail);
                Config::set('mail.mailers.smtp.from.address', $fromEmail);
            }
            if ($fromName !== '') {
                Config::set('mail.from.name', $fromName);
                Config::set('mail.mailers.smtp.from.name', $fromName);
            }

            // Purge cached mailer transport if mail manager has already been resolved
            if (app()->resolved('mail.manager')) {
                Mail::purge('smtp');
            }

            return true;
        } catch (\Throwable $e) {
            Log::debug('MailSettingsService: failed to apply settings: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get the SMTP settings array from cache or DB.
     *
     * @return array<string, mixed>
     */
    public static function getSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $keys = [
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password_encrypted',
                'smtp_password',
                'smtp_from_name',
                'smtp_from_email',
            ];

            return Setting::whereIn('key', $keys)->get()
                ->mapWithKeys(fn ($s) => [$s->key => $s->value['val'] ?? $s->value])
                ->toArray();
        });
    }

    /**
     * Clear the cached SMTP settings and re-apply.
     */
    public static function clearCacheAndReapply(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('thh:smtp_settings_test');
        self::apply();
    }
}
