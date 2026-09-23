<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Settings\Models\AuditLog;
use App\Domains\Settings\Models\StaticPage;
use App\Http\Controllers\Controller;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaticPageController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/pages/index', [
            'pages' => StaticPage::query()->orderBy('slug')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $page = StaticPage::findOrFail($id);

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_gu' => ['required', 'string', 'max:255'],
            'body_en' => ['required', 'string'],
            'body_gu' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $before = $page->only(['title_en', 'title_gu', 'is_active']);

        $page->update([
            'title_en' => $validated['title_en'],
            'title_gu' => $validated['title_gu'],
            'body_en' => HtmlSanitizer::clean($validated['body_en']),
            'body_gu' => HtmlSanitizer::clean($validated['body_gu']),
            'title_key' => $validated['title_en'],
            'content_key' => strip_tags($validated['body_en']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLog::record(
            action: 'cms.page.update',
            subject: $page,
            before: $before,
            after: $page->only(['title_en', 'title_gu', 'is_active']),
            actorId: $request->user()?->id
        );

        return back()->with('success', 'Page updated.');
    }
}
