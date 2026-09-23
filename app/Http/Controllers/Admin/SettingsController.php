<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Settings\Branding;
use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * GET /admin/settings — render settings page with all current values.
     */
    public function index(): Response
    {
        $settings = Setting::all()->mapWithKeys(function ($s) {
            $val = $s->value['val'] ?? $s->value;

            return [$s->key => $val];
        });

        // Mask the SMTP password from the response
        if (isset($settings['smtp_password_encrypted']) && ! empty($settings['smtp_password_encrypted'])) {
            $settings['smtp_password_masked'] = '••••••••';
        }
        unset($settings['smtp_password_encrypted']);

        $notificationTemplates = NotificationTemplate::all();

        $settings['app_logo_url'] = Branding::logoUrl(
            is_string($settings['app_logo_path'] ?? null) ? $settings['app_logo_path'] : null
        );
        $settings['app_favicon_url'] = Branding::logoUrl(
            is_string($settings['app_favicon_path'] ?? null) ? $settings['app_favicon_path'] : null
        );

        return Inertia::render('admin/settings/index', [
            'settings' => $settings,
            'notification_templates' => $notificationTemplates,
        ]);
    }

    /**
     * POST /admin/settings/general — update general app settings.
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name_en' => ['required', 'string', 'max:100'],
            'app_name_gu' => ['required', 'string', 'max:100'],
            'app_name_short_en' => ['nullable', 'string', 'max:20'],
            'app_name_short_gu' => ['nullable', 'string', 'max:20'],
            'support_email' => ['required', 'email', 'max:200'],
            'helpline_phone' => ['nullable', 'string', 'max:30'],
            'public_portal_enabled' => ['nullable', 'boolean'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
            'app_favicon' => ['nullable', 'image', 'mimes:png,ico,svg,jpeg,jpg,webp', 'max:512'],
        ]);

        foreach (['app_name_en', 'app_name_gu', 'app_name_short_en', 'app_name_short_gu', 'support_email', 'helpline_phone'] as $key) {
            Setting::set($key, $validated[$key] ?? '', 'general');
        }

        if ($request->has('public_portal_enabled')) {
            Setting::set('public_portal_enabled', $request->boolean('public_portal_enabled'), 'general');
        }

        if ($request->hasFile('app_logo')) {
            $previous = Setting::get('app_logo_path');
            $path = $request->file('app_logo')->store('branding', 'public');
            Setting::set('app_logo_path', $path, 'general');
            if (is_string($previous) && $previous !== '' && $previous !== $path) {
                Storage::disk('public')->delete($previous);
            }
        }

        if ($request->hasFile('app_favicon')) {
            $previous = Setting::get('app_favicon_path');
            $path = $request->file('app_favicon')->store('branding', 'public');
            Setting::set('app_favicon_path', $path, 'general');
            if (is_string($previous) && $previous !== '' && $previous !== $path) {
                Storage::disk('public')->delete($previous);
            }
        }

        Cache::forget('thh:settings_all');

        return back()->with('success', 'General settings updated successfully.');
    }

    /**
     * POST /admin/settings/smtp — save SMTP credentials (password encrypted).
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['required', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:500'],
            'smtp_from_name' => ['required', 'string', 'max:100'],
            'smtp_from_email' => ['required', 'email', 'max:200'],
        ]);

        Setting::set('smtp_host', $validated['smtp_host'], 'smtp');
        Setting::set('smtp_port', $validated['smtp_port'], 'smtp');
        Setting::set('smtp_username', $validated['smtp_username'], 'smtp');
        Setting::set('smtp_from_name', $validated['smtp_from_name'], 'smtp');
        Setting::set('smtp_from_email', $validated['smtp_from_email'], 'smtp');

        // Only update password if one was provided (avoid overwriting with empty)
        if (! empty($validated['smtp_password'])) {
            Setting::set('smtp_password_encrypted', encrypt($validated['smtp_password']), 'smtp');
        }

        Cache::forget('thh:smtp_settings');

        return back()->with('success', 'SMTP settings saved. Use the Test Email button to verify.');
    }

    /**
     * POST /admin/settings/test-email — send a test email using stored SMTP.
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_recipient' => ['required', 'email'],
        ]);

        $smtp = Cache::remember('thh:smtp_settings_test', 5, function () {
            $keys = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password_encrypted', 'smtp_from_name', 'smtp_from_email'];

            return Setting::whereIn('key', $keys)->get()
                ->mapWithKeys(fn ($s) => [$s->key => $s->value['val'] ?? $s->value])
                ->toArray();
        });

        if (empty($smtp['smtp_host'])) {
            return back()->with('error', 'SMTP is not configured. Please save SMTP settings first.');
        }

        try {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $smtp['smtp_host']);
            Config::set('mail.mailers.smtp.port', (int) ($smtp['smtp_port'] ?? 587));
            Config::set('mail.mailers.smtp.username', $smtp['smtp_username'] ?? '');
            $rawPw = $smtp['smtp_password_encrypted'] ?? '';
            $password = '';
            if (! empty($rawPw)) {
                try {
                    $password = decrypt($rawPw);
                } catch (\Throwable) {
                    $password = $rawPw;
                }
            }
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', 'tls');
            Config::set('mail.from.name', $smtp['smtp_from_name'] ?? 'THH');
            Config::set('mail.from.address', $smtp['smtp_from_email'] ?? 'noreply@ggvt.org');

            Mail::html(
                '<h2 style="color:#B45309">🤝 Tribal Helping Hand — Test Email</h2><p>Your SMTP configuration is working correctly! Sent from the THH Admin Panel.</p>',
                fn ($msg) => $msg->to($validated['test_recipient'])->subject('THH — SMTP Test Email')
            );

            return back()->with('success', "Test email sent successfully to {$validated['test_recipient']}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Test email failed: '.$e->getMessage());
        }
    }

    /**
     * POST /admin/settings/notifications — update global notification channel toggles.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_notifications_enabled' => ['required', 'boolean'],
            'push_notifications_enabled' => ['required', 'boolean'],
        ]);

        Setting::set('email_notifications_enabled', $validated['email_notifications_enabled'], 'notifications');
        Setting::set('push_notifications_enabled', $validated['push_notifications_enabled'], 'notifications');

        return back()->with('success', 'Notification channel settings updated.');
    }

    /**
     * POST /admin/settings/notification-template/{id} — toggle a specific notification template on/off.
     */
    public function toggleTemplate(Request $request, int $id): RedirectResponse
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->update(['is_enabled' => ! $template->is_enabled]);

        $state = $template->is_enabled ? 'enabled' : 'disabled';

        return back()->with('success', "Notification template '{$template->event_key}' {$state}.");
    }
}
