<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    /**
     * Display location hierarchy: Districts, Talukas, and Villages / Cities.
     */
    public function index(Request $request): Response
    {
        $selectedDistrictId = $request->query('district_id');
        $selectedTalukaId = $request->query('taluka_id');
        $search = $request->query('search', '');

        $districtsQuery = District::with(['talukas' => function ($q) {
            $q->withCount('villages')->orderBy('name_en');
        }])->orderBy('name_en');

        $districts = $districtsQuery->get();

        $activeDistrict = null;
        if ($selectedDistrictId) {
            $activeDistrict = $districts->firstWhere('id', (int) $selectedDistrictId);
        }
        if (! $activeDistrict && $districts->isNotEmpty()) {
            $activeDistrict = $districts->first();
        }

        $activeTaluka = null;
        if ($activeDistrict) {
            if ($selectedTalukaId) {
                $activeTaluka = $activeDistrict->talukas->firstWhere('id', (int) $selectedTalukaId);
            }
            if (! $activeTaluka && $activeDistrict->talukas->isNotEmpty()) {
                $activeTaluka = $activeDistrict->talukas->first();
            }
        }

        $villages = collect();
        if ($activeTaluka) {
            $vQuery = Village::where('taluka_id', $activeTaluka->id);
            if (! empty($search)) {
                $vQuery->where(function ($q) use ($search) {
                    $q->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_gu', 'like', "%{$search}%")
                        ->orWhere('pincode', 'like', "%{$search}%");
                });
            }
            $villages = $vQuery->orderBy('name_en')->get();
        }

        $stats = [
            'total_districts' => District::count(),
            'total_talukas' => Taluka::count(),
            'total_villages' => Village::count(),
            'active_villages' => Village::where('is_active', true)->count(),
        ];

        return Inertia::render('admin/locations/index', [
            'districts' => $districts,
            'activeDistrictId' => $activeDistrict?->id,
            'activeTalukaId' => $activeTaluka?->id,
            'villages' => $villages,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Store new District.
     */
    public function storeDistrict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:districts,code'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $district = District::create([
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'code' => strtoupper(trim($validated['code'])),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.district.create',
            subject: $district,
            before: null,
            after: $district->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "District '{$district->name_en}' created successfully.");
    }

    /**
     * Update District.
     */
    public function updateDistrict(Request $request, int $id): RedirectResponse
    {
        $district = District::findOrFail($id);
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:districts,code,'.$id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $district->toArray();
        $district->update([
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'code' => strtoupper(trim($validated['code'])),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.district.update',
            subject: $district,
            before: $before,
            after: $district->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "District '{$district->name_en}' updated successfully.");
    }

    /**
     * Store new Taluka.
     */
    public function storeTaluka(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $taluka = Taluka::create([
            'district_id' => $validated['district_id'],
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'code' => ! empty($validated['code']) ? strtoupper(trim($validated['code'])) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.taluka.create',
            subject: $taluka,
            before: null,
            after: $taluka->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "Taluka '{$taluka->name_en}' created successfully.");
    }

    /**
     * Update Taluka.
     */
    public function updateTaluka(Request $request, int $id): RedirectResponse
    {
        $taluka = Taluka::findOrFail($id);
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $taluka->toArray();
        $taluka->update([
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'code' => ! empty($validated['code']) ? strtoupper(trim($validated['code'])) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.taluka.update',
            subject: $taluka,
            before: $before,
            after: $taluka->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "Taluka '{$taluka->name_en}' updated successfully.");
    }

    /**
     * Store new Village / City.
     */
    public function storeVillage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'taluka_id' => ['required', 'exists:talukas,id'],
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $village = Village::create([
            'taluka_id' => $validated['taluka_id'],
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'pincode' => $validated['pincode'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.village.create',
            subject: $village,
            before: null,
            after: $village->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "Village / City '{$village->name_en}' added successfully.");
    }

    /**
     * Update Village / City.
     */
    public function updateVillage(Request $request, int $id): RedirectResponse
    {
        $village = Village::findOrFail($id);
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:100'],
            'name_gu' => ['required', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $village->toArray();
        $village->update([
            'name_en' => $validated['name_en'],
            'name_gu' => $validated['name_gu'],
            'pincode' => $validated['pincode'] ?? null,
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'location.village.update',
            subject: $village,
            before: $before,
            after: $village->toArray(),
            actorId: $request->user()?->id
        );

        return back()->with('success', "Village / City '{$village->name_en}' updated successfully.");
    }

    /**
     * Toggle active state of any location entity.
     */
    public function toggleActive(Request $request, string $type, int $id): RedirectResponse
    {
        $model = match ($type) {
            'district' => District::findOrFail($id),
            'taluka' => Taluka::findOrFail($id),
            'village' => Village::findOrFail($id),
            default => abort(404),
        };

        $model->is_active = ! $model->is_active;
        $model->save();

        return back()->with('success', "Updated active status for {$model->name_en}.");
    }
}
