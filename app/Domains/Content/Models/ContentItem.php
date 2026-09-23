<?php

namespace App\Domains\Content\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $module_id
 * @property string $slug
 * @property string $title_en
 * @property string|null $title_gu
 * @property string|null $excerpt_en
 * @property string|null $excerpt_gu
 * @property string|null $body_en
 * @property string|null $body_gu
 * @property string|null $cover_image
 * @property array<string, mixed>|null $meta
 * @property bool $is_published
 * @property int $sort_order
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property ContentModule|null $module
 */
class ContentItem extends Model
{
    protected $fillable = [
        'module_id',
        'slug',
        'title_en',
        'title_gu',
        'excerpt_en',
        'excerpt_gu',
        'body_en',
        'body_gu',
        'cover_image',
        'meta',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(ContentModule::class, 'module_id');
    }

    public function localizedTitle(string $locale = 'en'): string
    {
        return $locale === 'gu' ? ($this->title_gu ?: $this->title_en) : $this->title_en;
    }

    public function localizedExcerpt(string $locale = 'en'): ?string
    {
        return $locale === 'gu' ? ($this->excerpt_gu ?: $this->excerpt_en) : $this->excerpt_en;
    }

    public function localizedBody(string $locale = 'en'): ?string
    {
        return $locale === 'gu' ? ($this->body_gu ?: $this->body_en) : $this->body_en;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
