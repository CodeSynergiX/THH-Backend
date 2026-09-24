<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Cases\Models\Application;
use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\ContentItem;
use App\Domains\Content\Models\ContentModule;
use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\StaticPage;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use App\Domains\Settings\Resources\ThemeVersionResource;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use App\Http\Controllers\Controller;
use App\Models\User;
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
        $locale = $request->query('locale', 'en');
        $modules = ContentModule::query()
            ->public()
            ->where('slug', '!=', 'home')
            ->withCount('publishedItems')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ContentModule $module) => [
                'key' => $module->slug,
                'slug' => $module->slug,
                'title' => $module->localizedTitle($locale),
                'title_en' => $module->title_en,
                'title_gu' => $module->title_gu,
                'subtitle' => $module->localizedDescription($locale),
                'subtitle_en' => $module->description_en,
                'subtitle_gu' => $module->description_gu,
                'icon' => $module->icon,
                'accent_color' => $module->accent_color,
                'show_apply_form' => $module->show_apply_form,
                'count' => $module->published_items_count,
                'target_route' => 'module',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Home tiles loaded.',
            'data' => $modules,
        ]);
    }

    public function modules(Request $request): JsonResponse
    {
        return $this->homeTiles($request);
    }

    public function moduleItems(Request $request, string $module): JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $record = ContentModule::query()->public()->where('slug', $module)->firstOrFail();
        $items = $record->publishedItems()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'module' => [
                    'slug' => $record->slug,
                    'title' => $record->localizedTitle($locale),
                    'description' => $record->localizedDescription($locale),
                    'show_apply_form' => $record->show_apply_form,
                    'accent_color' => $record->accent_color,
                    'icon' => $record->icon,
                ],
                'items' => $items->through(fn (ContentItem $item) => [
                    'id' => $item->id,
                    'slug' => $item->slug,
                    'title' => $item->localizedTitle($locale),
                    'excerpt' => $item->localizedExcerpt($locale),
                    'cover_image' => $item->cover_image,
                    'meta' => $item->meta,
                ]),
            ],
        ]);
    }

    public function moduleItem(Request $request, string $module, string $item): JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $record = ContentModule::query()->public()->where('slug', $module)->firstOrFail();
        $content = $record->publishedItems()->where('slug', $item)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'module' => ['slug' => $record->slug, 'title' => $record->localizedTitle($locale)],
                'item' => [
                    'id' => $content->id,
                    'slug' => $content->slug,
                    'title' => $content->localizedTitle($locale),
                    'excerpt' => $content->localizedExcerpt($locale),
                    'body' => $content->localizedBody($locale),
                    'meta' => $content->meta,
                ],
            ],
        ]);
    }

    public function staticPage(Request $request, string $slug): JsonResponse
    {
        $locale = $request->query('locale', 'en');
        $page = StaticPage::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $page->slug,
                'title' => $page->localizedTitle($locale),
                'body' => $page->localizedBody($locale),
            ],
        ]);
    }

    public function publicStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'resolved_cases' => Application::query()->where('status', 'resolved')->count(),
                'citizens_helped' => User::query()->whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->count(),
                'villages_covered' => Village::query()->where('is_active', true)->count(),
            ],
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

    public function categories(): JsonResponse
    {
        $categories = Category::with('subCategories')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function districts(): JsonResponse
    {
        $districts = District::with(['talukas.villages'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $districts,
        ]);
    }

    public function nearestLocation(Request $request): JsonResponse
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return response()->json([
                'success' => false,
                'message' => 'Valid numeric lat and lng query parameters are required.',
            ], 422);
        }

        $userLat = (float) $lat;
        $userLng = (float) $lng;

        $villages = Village::with(['taluka.district'])
            ->where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        if ($villages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No geo-referenced locations found in the database.',
            ], 404);
        }

        $nearest = null;
        $minDistance = INF;

        foreach ($villages as $village) {
            $vLat = (float) $village->lat;
            $vLng = (float) $village->lng;

            // Haversine calculation
            $dLat = deg2rad($vLat - $userLat);
            $dLon = deg2rad($vLng - $userLng);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($userLat)) * cos(deg2rad($vLat)) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distKm = 6371 * $c;

            if ($distKm < $minDistance) {
                $minDistance = $distKm;
                $nearest = $village;
            }
        }

        if (! $nearest) {
            return response()->json([
                'success' => false,
                'message' => 'Could not determine nearest location.',
            ], 404);
        }

        $taluka = $nearest->taluka;
        $district = $taluka?->district;

        return response()->json([
            'success' => true,
            'message' => 'Nearest village located successfully.',
            'data' => [
                'village' => [
                    'id' => $nearest->id,
                    'name_en' => $nearest->name_en,
                    'name_gu' => $nearest->name_gu,
                    'pincode' => $nearest->pincode,
                    'lat' => (float) $nearest->lat,
                    'lng' => (float) $nearest->lng,
                ],
                'taluka' => $taluka ? [
                    'id' => $taluka->id,
                    'name_en' => $taluka->name_en,
                    'name_gu' => $taluka->name_gu,
                    'code' => $taluka->code,
                ] : null,
                'district' => $district ? [
                    'id' => $district->id,
                    'name_en' => $district->name_en,
                    'name_gu' => $district->name_gu,
                    'code' => $district->code,
                ] : null,
                'distance_km' => round($minDistance, 2),
            ],
        ]);
    }

    public function talukas(Request $request): JsonResponse
    {
        $districtId = $request->query('district_id');
        $query = Taluka::query()->where('is_active', true)->with('villages');
        if ($districtId) {
            $query->where('district_id', $districtId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function villages(Request $request): JsonResponse
    {
        $talukaId = $request->query('taluka_id');
        $query = Village::query()->where('is_active', true);
        if ($talukaId) {
            $query->where('taluka_id', $talukaId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get system workflow stages configured for applications.
     */
    public function workflowStages(Request $request): JsonResponse
    {
        $status = $request->query('status');

        return response()->json([
            'success' => true,
            'message' => 'Workflow stages loaded.',
            'data' => Application::getWorkflowStagesFor($status),
        ]);
    }
}
