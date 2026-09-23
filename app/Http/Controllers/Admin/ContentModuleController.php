<?php

namespace App\Http\Controllers\Admin;

use App\Domains\Content\Models\ContentModule;
use App\Domains\Settings\Models\AuditLog;
use App\Http\Controllers\Controller;
use App\Support\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContentModuleController extends Controller
{
    public function index(): Response
    {
        $modules = ContentModule::query()
            ->withCount(['items', 'publishedItems'])
            ->orderBy('sort_order')
            ->orderBy('title_en')
            ->get()
            ->map(fn (ContentModule $module) => [
                'id' => $module->id,
                'slug' => $module->slug,
                'title' => $module->title_en,
                'title_en' => $module->title_en,
                'title_gu' => $module->title_gu,
                'description' => $module->description_en,
                'description_en' => $module->description_en,
                'description_gu' => $module->description_gu,
                'icon' => $module->icon,
                'accent_color' => $module->accent_color,
                'is_enabled' => $module->is_enabled,
                'is_public' => $module->is_public,
                'show_apply_form' => $module->show_apply_form,
                'type' => $module->type,
                'sort_order' => $module->sort_order,
                'count' => $module->items_count,
                'active_count' => $module->published_items_count,
            ]);

        return Inertia::render('admin/content/index', [
            'modules' => $modules,
        ]);
    }

    public function storeRegistry(Request $request): RedirectResponse
    {
        $validated = $this->validatedModule($request);

        $module = ContentModule::create([
            ...$validated,
            'slug' => $validated['slug'] ?: Str::slug($validated['title_en']),
        ]);

        AuditLog::record(
            action: 'content.module.create',
            subject: $module,
            before: null,
            after: $module->only(['slug', 'title_en', 'is_public']),
            actorId: $request->user()?->id
        );

        return back()->with('success', 'Module created.');
    }

    public function updateRegistry(Request $request, int $id): RedirectResponse
    {
        $module = ContentModule::findOrFail($id);
        $validated = $this->validatedModule($request, $module->id);
        $before = $module->only(['slug', 'title_en', 'is_public', 'is_enabled']);

        $module->update([
            ...$validated,
            'slug' => $validated['slug'] ?: $module->slug,
        ]);

        AuditLog::record(
            action: 'content.module.update',
            subject: $module,
            before: $before,
            after: $module->only(['slug', 'title_en', 'is_public', 'is_enabled']),
            actorId: $request->user()?->id
        );

        return back()->with('success', 'Module updated.');
    }

    public function destroyRegistry(Request $request, int $id): RedirectResponse
    {
        $module = ContentModule::findOrFail($id);
        $slug = $module->slug;
        $module->delete();

        AuditLog::record(
            action: 'content.module.delete',
            subject: $request->user(),
            before: ['slug' => $slug],
            after: null,
            actorId: $request->user()?->id
        );

        return redirect()->route('admin.content.index')->with('success', 'Module removed.');
    }

    public function showModule(Request $request, string $module): Response
    {
        $record = ContentModule::query()->where('slug', $module)->firstOrFail();
        $search = (string) $request->query('search', '');

        $items = $record->items()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title_en', 'like', "%{$search}%")
                        ->orWhere('title_gu', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/content/manage', [
            'meta' => [
                'id' => $record->id,
                'slug' => $record->slug,
                'title' => $record->title_en,
                'title_en' => $record->title_en,
                'title_gu' => $record->title_gu,
                'description' => $record->description_en,
                'singular' => 'Item',
                'type' => $record->type,
                'show_apply_form' => $record->show_apply_form,
            ],
            'items' => [
                'data' => $items->items(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'total' => $items->total(),
                'per_page' => $items->perPage(),
            ],
            'filters' => ['search' => $search],
        ]);
    }

    public function storeModuleItem(Request $request, string $module): RedirectResponse
    {
        $record = ContentModule::query()->where('slug', $module)->firstOrFail();
        $payload = $this->itemPayload($request, $record);

        $item = $record->items()->create($payload);

        AuditLog::record(
            action: 'content.item.create',
            subject: $item,
            before: null,
            after: $item->only(['slug', 'title_en']),
            actorId: $request->user()?->id
        );

        return back()->with('success', 'Item created.');
    }

    public function updateModuleItem(Request $request, string $module, int $id): RedirectResponse
    {
        $record = ContentModule::query()->where('slug', $module)->firstOrFail();
        $item = $record->items()->findOrFail($id);
        $payload = $this->itemPayload($request, $record, $item->id);

        $item->update($payload);

        return back()->with('success', 'Item updated.');
    }

    public function destroyModuleItem(Request $request, string $module, int $id): RedirectResponse
    {
        $record = ContentModule::query()->where('slug', $module)->firstOrFail();
        $item = $record->items()->findOrFail($id);
        $item->delete();

        return back()->with('success', 'Item removed.');
    }

    public function toggleModuleItem(Request $request, string $module, int $id): RedirectResponse
    {
        $record = ContentModule::query()->where('slug', $module)->firstOrFail();
        $item = $record->items()->findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedModule(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = ['nullable', 'string', 'max:80', 'alpha_dash'];
        if ($ignoreId) {
            $slugRule[] = 'unique:content_modules,slug,'.$ignoreId;
        } else {
            $slugRule[] = 'unique:content_modules,slug';
        }

        return $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_gu' => ['required', 'string', 'max:255'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'description_gu' => ['nullable', 'string', 'max:1000'],
            'slug' => $slugRule,
            'icon' => ['nullable', 'string', 'max:50'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'is_enabled' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'show_apply_form' => ['sometimes', 'boolean'],
            'type' => ['nullable', 'string', 'in:cms,operational'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function itemPayload(Request $request, ContentModule $module, ?int $ignoreId = null): array
    {
        $title = $request->input('title_en') ?: $request->input('title');
        $excerpt = $request->input('excerpt_en') ?: $request->input('benefit_summary');
        $body = $request->input('body_en') ?: ($excerpt ? '<p>'.e((string) $excerpt).'</p>' : '');

        $request->merge([
            'title_en' => $title,
            'title_gu' => $request->input('title_gu') ?: $title,
            'excerpt_en' => $excerpt,
            'excerpt_gu' => $request->input('excerpt_gu') ?: $excerpt,
            'body_en' => $body,
            'body_gu' => $request->input('body_gu') ?: $body,
        ]);

        $slugRules = ['nullable', 'string', 'max:120'];
        $slugRules[] = $ignoreId
            ? 'unique:content_items,slug,'.$ignoreId
            : 'unique:content_items,slug';

        $validated = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_gu' => ['required', 'string', 'max:255'],
            'excerpt_en' => ['nullable', 'string', 'max:500'],
            'excerpt_gu' => ['nullable', 'string', 'max:500'],
            'body_en' => ['nullable', 'string'],
            'body_gu' => ['nullable', 'string'],
            'slug' => $slugRules,
            'is_published' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
        ]);

        $meta = $validated['meta'] ?? [];
        if ($request->filled('documents')) {
            $meta['required_documents'] = array_map('trim', explode(',', (string) $request->input('documents')));
        }
        if ($request->filled('amount')) {
            $meta['amount'] = $request->input('amount');
        }
        if ($request->filled('company')) {
            $meta['company'] = $request->input('company');
            $meta['location'] = $request->input('location');
            $meta['salary_range'] = $request->input('salary_range');
        }

        return [
            'slug' => $validated['slug'] ?: Str::slug($validated['title_en']).'-'.Str::lower(Str::random(4)),
            'title_en' => $validated['title_en'],
            'title_gu' => $validated['title_gu'],
            'excerpt_en' => $validated['excerpt_en'] ?? null,
            'excerpt_gu' => $validated['excerpt_gu'] ?? null,
            'body_en' => HtmlSanitizer::clean($validated['body_en'] ?? null),
            'body_gu' => HtmlSanitizer::clean($validated['body_gu'] ?? null),
            'meta' => $meta,
            'is_published' => $request->boolean('is_published', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }
}
