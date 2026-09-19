<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Settings\Models\ThemeVersion;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ThemeController extends Controller
{
    /**
     * Display the Theme & Appearance management interface.
     */
    public function index(): Response
    {
        $current = ThemeVersion::currentPublished() ?? ThemeVersion::first();
        $history = ThemeVersion::with('publisher:id,name')
            ->orderBy('version', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('admin/theme/index', [
            'current' => $current,
            'history' => $history,
        ]);
    }

    /**
     * Publish a new version of the theme tokens.
     */
    public function publish(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'light' => ['required', 'array'],
            'dark' => ['required', 'array'],
            'notes' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($validated, $user) {
            // Unpublish previous versions
            ThemeVersion::where('is_published', true)->update(['is_published' => false]);

            $latestVersionNumber = (int) (ThemeVersion::max('version') ?? 0);
            $newVersionNumber = $latestVersionNumber + 1;

            $newTheme = ThemeVersion::create([
                'version' => $newVersionNumber,
                'light' => $validated['light'],
                'dark' => $validated['dark'],
                'meta' => $validated['meta'] ?? [],
                'is_published' => true,
                'published_by' => $user?->id,
                'published_at' => now(),
                'notes' => $validated['notes'] ?? "Published by {$user?->name}",
            ]);

            AuditLog::record(
                action: 'theme.publish',
                subject: $newTheme,
                before: null,
                after: ['version' => $newVersionNumber],
                actorId: $user?->id
            );
        });

        return back()->with('success', 'Theme tokens published successfully!');
    }

    /**
     * Rollback to a specific past theme version.
     */
    public function rollback(Request $request, int $id): RedirectResponse
    {
        $target = ThemeVersion::findOrFail($id);
        $user = $request->user();

        DB::transaction(function () use ($target, $user) {
            ThemeVersion::where('is_published', true)->update(['is_published' => false]);

            $target->is_published = true;
            $target->published_at = now();
            $target->published_by = $user?->id;
            $target->save();

            AuditLog::record(
                action: 'theme.rollback',
                subject: $target,
                before: null,
                after: ['rolled_back_to_version' => $target->version],
                actorId: $user?->id
            );
        });

        return back()->with('success', "Rolled back to Theme Version #{$target->version}.");
    }
}
