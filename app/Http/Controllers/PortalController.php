<?php

namespace App\Http\Controllers;

use App\Domains\Cases\Models\Application;
use App\Domains\Content\Models\Category;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    /**
     * Render the public citizen portal homepage with live database stats.
     */
    public function index(Request $request): Response
    {
        $resolvedCount = Application::where('status', Application::STATUS_RESOLVED)->count();
        $citizensCount = User::whereHas('roles', fn ($q) => $q->where('name', 'citizen'))->count();
        $villagesCount = Village::where('is_active', true)->count();

        $categories = Category::where('is_active', true)
            ->withCount('applications')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('welcome', [
            'stats' => [
                'resolved_cases' => $resolvedCount,
                'citizens_helped' => $citizensCount,
                'villages_covered' => $villagesCount,
            ],
            'categories' => $categories,
        ]);
    }

    /**
     * Quick case status lookup by Case Number (e.g. THH-2026-00001).
     */
    public function track(Request $request, string $caseNo)
    {
        $case = Application::where('case_no', strtoupper(trim($caseNo)))
            ->with(['category', 'village.taluka.district'])
            ->first();

        if (! $case) {
            return response()->json([
                'success' => false,
                'message' => 'Case not found with the provided case number.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'case_no' => $case->case_no,
                'status' => $case->status,
                'title' => $case->title,
                'category' => $case->category?->slug,
                'district' => $case->village?->taluka?->district?->name_en,
                'created_at' => $case->created_at?->toIso8601String(),
                'resolved_at' => $case->resolved_at?->toIso8601String(),
            ],
        ]);
    }
}
