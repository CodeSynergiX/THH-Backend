<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\Translation;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocalizationController extends Controller
{
    /**
     * Display the Localization & Translations manager interface.
     */
    public function index(Request $request): Response
    {
        $selectedLocale = $request->query('locale', 'gu');
        $selectedGroup = $request->query('group', 'all');
        $search = $request->query('search', '');

        $languages = Language::orderBy('is_default', 'desc')->get();

        $groups = Translation::select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->all();

        $query = Translation::query();

        if ($selectedGroup !== 'all') {
            $query->where('group', $selectedGroup);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        // Fetch translation keys with values for both Gujarati and English
        $translations = $query->orderBy('group')->orderBy('key')->get();

        // Calculate language completion stats
        $stats = [];
        $totalGu = Translation::where('locale', 'gu')->count();
        $totalEn = Translation::where('locale', 'en')->count();
        $uniqueKeys = Translation::select('group', 'key')->distinct()->count();

        $stats['gu'] = [
            'total' => $totalGu,
            'percent' => $uniqueKeys > 0 ? round(($totalGu / $uniqueKeys) * 100) : 100,
        ];
        $stats['en'] = [
            'total' => $totalEn,
            'percent' => $uniqueKeys > 0 ? round(($totalEn / $uniqueKeys) * 100) : 100,
        ];

        return Inertia::render('admin/localization/index', [
            'languages' => $languages,
            'groups' => $groups,
            'translations' => $translations,
            'selectedLocale' => $selectedLocale,
            'selectedGroup' => $selectedGroup,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Update or create a translation key value.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group' => ['required', 'string', 'max:50'],
            'key' => ['required', 'string', 'max:100'],
            'locale' => ['required', 'string', 'max:10'],
            'value' => ['required', 'string'],
        ]);

        $user = $request->user();

        $translation = Translation::updateOrCreate(
            [
                'group' => $validated['group'],
                'key' => $validated['key'],
                'locale' => $validated['locale'],
            ],
            [
                'value' => $validated['value'],
                'needs_review' => false,
                'updated_by' => $user?->id,
            ]
        );

        AuditLog::record(
            action: 'translation.update',
            subject: $translation,
            before: null,
            after: [
                'group' => $validated['group'],
                'key' => $validated['key'],
                'locale' => $validated['locale'],
                'value' => $validated['value'],
            ],
            actorId: $user?->id
        );

        return back()->with('success', 'Translation updated successfully.');
    }

    /**
     * Scan and return list of missing keys across languages.
     */
    public function scanMissing(): JsonResponse
    {
        $allKeys = Translation::select('group', 'key')->distinct()->get();
        $locales = Language::where('is_enabled', true)->pluck('code')->all();

        $missing = [];
        foreach ($allKeys as $row) {
            foreach ($locales as $locale) {
                $exists = Translation::where('group', $row->group)
                    ->where('key', $row->key)
                    ->where('locale', $locale)
                    ->exists();

                if (! $exists) {
                    $missing[] = [
                        'group' => $row->group,
                        'key' => $row->key,
                        'missing_in' => $locale,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'count' => count($missing),
            'missing' => $missing,
        ]);
    }

    /**
     * Export translations as JSON.
     */
    public function exportJson(string $locale): StreamedResponse
    {
        $translations = Translation::where('locale', $locale)->get();
        $payload = [];
        foreach ($translations as $item) {
            $payload["{$item->group}.{$item->key}"] = $item->value;
        }

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, "translations_{$locale}.json", [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Export translations as CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['group', 'key', 'locale', 'value']);

            Translation::orderBy('group')->orderBy('key')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [$row->group, $row->key, $row->locale, $row->value]);
                }
            });

            fclose($handle);
        }, 'translations.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
