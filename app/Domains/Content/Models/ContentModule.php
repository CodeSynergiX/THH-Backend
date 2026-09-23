<?php

namespace App\Domains\Content\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $title_en
 * @property string|null $title_gu
 * @property string|null $description_en
 * @property string|null $description_gu
 * @property string|null $icon
 * @property string|null $accent_color
 * @property bool $is_enabled
 * @property bool $is_public
 * @property int $sort_order
 * @property bool $show_apply_form
 * @property string $type
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property Collection<int, ContentItem> $items
 * @property Collection<int, ContentItem> $publishedItems
 */
class ContentModule extends Model
{
    protected $fillable = [
        'slug',
        'title_en',
        'title_gu',
        'description_en',
        'description_gu',
        'icon',
        'accent_color',
        'is_enabled',
        'is_public',
        'sort_order',
        'show_apply_form',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_public' => 'boolean',
            'show_apply_form' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<ContentItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ContentItem::class, 'module_id');
    }

    /**
     * @return HasMany<ContentItem, $this>
     */
    public function publishedItems(): HasMany
    {
        return $this->items()->where('is_published', true)->orderBy('sort_order')->orderByDesc('id');
    }

    public function localizedTitle(string $locale = 'en'): string
    {
        return $locale === 'gu' ? ($this->title_gu ?: $this->title_en) : $this->title_en;
    }

    public function localizedDescription(string $locale = 'en'): ?string
    {
        return $locale === 'gu' ? ($this->description_gu ?: $this->description_en) : $this->description_en;
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)->where('is_enabled', true);
    }
}
