<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Content\Models\Category;
use App\Domains\Settings\Models\HomeTile;
use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use App\Domains\Settings\Resources\ThemeVersionResource;
use App\Domains\Users\Models\District;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Get the active published theme tokens and metadata.
     */
    public function theme(): JsonResponse
    {
        $theme = ThemeVersion::currentPublished();

        if (! $theme) {
            return response()->json([
                'success' => false,
                'message' => 'No published theme found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme configuration loaded.',
            'data' => new ThemeVersionResource($theme),
        ]);
    }

    /**
     * Get active languages.
     */
    public function languages(): JsonResponse
    {
        $languages = Language::where('is_enabled', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Languages loaded.',
            'data' => $languages,
        ]);
    }

    /**
     * Get flat translations dictionary for specified locale.
     */
    public function translations(Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'gu');
        $translations = Translation::getFlatDictionary($locale);

        $versionHash = md5(json_encode($translations) ?: '');

        return response()->json([
            'success' => true,
            'message' => 'Translations loaded.',
            'data' => [
                'locale' => $locale,
                'version' => $versionHash,
                'translations' => $translations,
            ],
        ]);
    }

    /**
     * Get home tiles filtered by user's role.
     */
    public function homeTiles(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $userRole = $user ? $user->getRoleNames()->first() : 'citizen';

        $tiles = HomeTile::where('is_enabled', true)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->filter(function ($tile) use ($userRole) {
                if (empty($tile->visible_roles)) {
                    return true;
                }

                return in_array($userRole, $tile->visible_roles, true);
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Home tiles loaded.',
            'data' => $tiles,
        ]);
    }

    /**
     * Get master data (categories, districts, talukas, villages).
     */
    public function masterData(): JsonResponse
    {
        $categories = Category::with('subCategories')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $districts = District::with(['talukas.villages'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Master data loaded.',
            'data' => [
                'categories' => $categories,
                'districts' => $districts,
            ],
        ]);
    }
}
