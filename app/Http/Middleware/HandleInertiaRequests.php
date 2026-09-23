<?php

namespace App\Http\Middleware;

use App\Domains\Notifications\Models\NotificationLog;
use App\Domains\Settings\Branding;
use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use App\Domains\Users\Permissions\PermissionCatalog;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // 1. Resolve current locale
        $locale = $user->locale ?? $request->session()->get('locale', 'gu');
        app()->setLocale($locale);

        // 2. Fetch active theme
        $activeTheme = rescue(fn () => ThemeVersion::currentPublished(), null, false);

        // 3. Fetch translations dictionary for active locale
        $translations = rescue(fn () => Translation::getFlatDictionary($locale), [], false);

        // 4. Supported languages
        $supportedLocales = rescue(
            fn () => Language::where('is_enabled', true)
                ->get(['code', 'name', 'native_name', 'is_default'])
                ->toArray(),
            [],
            false
        );

        return [
            ...parent::share($request),
            'name' => config('app.name', 'Tribal Helping Hand'),
            'branding' => rescue(fn () => Branding::payload($locale), [], false),
            'locale' => $locale,
            'translations' => $translations,
            'supported_locales' => $supportedLocales,
            'theme' => [
                'version' => $activeTheme ? $activeTheme->version : 1,
                'light' => $activeTheme ? $activeTheme->light : [],
                'dark' => $activeTheme ? $activeTheme->dark : [],
                'meta' => $activeTheme ? $activeTheme->meta : [],
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'locale' => $user->locale ?? 'gu',
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->hasRole('super_admin')
                        ? PermissionCatalog::all()
                        : $user->getPermissionNames()->values()->all(),
                    'scope' => $user->getEffectiveScope(),
                    'district_id' => $user->district_id,
                    'taluka_id' => $user->taluka_id,
                    'village_id' => $user->village_id,
                ] : null,
            ],
            'unread_notifications' => $user
                ? rescue(fn () => NotificationLog::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count(), 0, false)
                : 0,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'status' => $request->session()->get('status'),
            ],
        ];
    }
}
